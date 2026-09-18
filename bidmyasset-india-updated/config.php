<?php
/**
 * PocketBase connection settings.
 *
 * This project used to talk to MySQL via phpMyAdmin. It now talks to a
 * PocketBase server instead (PocketBase stores everything in a single
 * SQLite file, so there is no separate database server to install).
 *
 * 1. Download PocketBase from https://pocketbase.io/docs/ and unzip it
 *    next to this project (or anywhere you like).
 * 2. Copy the pb_migrations/ folder from this project into PocketBase's
 *    own folder (so PocketBase picks it up on first run), then run:
 *       ./pocketbase serve
 *    This creates pb_data/data.db (the SQLite file) and applies the
 *    migrations automatically, which also creates:
 *       - a default admin login: admin@bidmyasset.com / admin123
 *       - the india/vietnam site settings, services and news rows
 * 3. Update PB_URL below if PocketBase is not running on the default
 *    address.
 */
define('PB_URL', getenv('PB_URL') ?: 'http://127.0.0.1:8090');

/**
 * Which country this deployment serves.
 *
 * This project now runs as two fully separate, independent websites — one
 * for India and one for Vietnam — each with its own PocketBase database,
 * its own domain, and its own admin logins. Both copies run the exact
 * same code; the only difference between them is this one setting, which
 * comes from the SITE_COUNTRY environment variable (see docker-compose.yml
 * — each deployment sets it to "india" or "vietnam"). This means a bug fix
 * or new feature only has to be written once and applies to both sites,
 * while the two sites' data can never mix because they're on completely
 * separate PocketBase instances.
 */
define('SITE_COUNTRY', in_array(strtolower(getenv('SITE_COUNTRY') ?: ''), ['india', 'vietnam'], true)
    ? strtolower(getenv('SITE_COUNTRY'))
    : 'india');

/**
 * Minimal PocketBase REST API client using cURL.
 * No external packages/SDKs needed — just plain PHP + cURL.
 */
class PocketBase
{
    /**
     * Send a request to the PocketBase API.
     *
     * @param string      $method HTTP method (GET, POST, PATCH, DELETE)
     * @param string      $path   API path, e.g. '/api/collections/news/records'
     * @param array|null  $body   Request body, will be sent as JSON
     * @param string|null $token  Bearer token (admin auth token), if any
     * @return array{status:int, data:array}
     */
    public static function request(string $method, string $path, ?array $body = null, ?string $token = null): array
    {
        $ch = curl_init(rtrim(PB_URL, '/') . $path);

        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 10,
        ];
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['status' => 0, 'data' => ['error' => $error]];
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        return ['status' => $status, 'data' => is_array($data) ? $data : []];
    }

    /** Escape a value for safe use inside a PocketBase filter string. */
    public static function escape(string $value): string
    {
        return str_replace("'", "\\'", $value);
    }

    /** Convenience GET returning the decoded 'items' list (or []). */
    public static function list(string $collection, array $params = [], ?string $token = null): array
    {
        $query = http_build_query($params);
        $res = self::request('GET', "/api/collections/$collection/records" . ($query ? "?$query" : ''), null, $token);
        return $res['data']['items'] ?? [];
    }

    /** Convenience GET returning just how many records match (ignores the actual rows). */
    public static function count(string $collection, array $params = [], ?string $token = null): int
    {
        $params['perPage'] = 1;
        $query = http_build_query($params);
        $res = self::request('GET', "/api/collections/$collection/records" . ($query ? "?$query" : ''), null, $token);
        return (int)($res['data']['totalItems'] ?? 0);
    }

    /** Convenience GET for a single record by id. */
    public static function view(string $collection, string $id, ?string $token = null): ?array
    {
        $res = self::request('GET', "/api/collections/$collection/records/$id", null, $token);
        return $res['status'] === 200 ? $res['data'] : null;
    }

    /** Convenience create. Returns the created record, or null on failure. */
    public static function create(string $collection, array $fields, ?string $token = null): ?array
    {
        $res = self::request('POST', "/api/collections/$collection/records", $fields, $token);
        return $res['status'] === 200 ? $res['data'] : null;
    }

    /** Convenience update. Returns the updated record, or null on failure. */
    public static function update(string $collection, string $id, array $fields, ?string $token = null): ?array
    {
        $res = self::request('PATCH', "/api/collections/$collection/records/$id", $fields, $token);
        return $res['status'] === 200 ? $res['data'] : null;
    }

    /** Convenience delete. Returns true on success. */
    public static function delete(string $collection, string $id, ?string $token = null): bool
    {
        $res = self::request('DELETE', "/api/collections/$collection/records/$id", null, $token);
        return $res['status'] === 204;
    }

    /**
     * Create or update a record that includes file uploads (images), using
     * multipart/form-data — required by PocketBase whenever a file field is
     * involved (plain JSON requests can't carry binary file data).
     *
     * @param string      $collection
     * @param string|null $id     Pass null to create a new record, or an existing record id to update it.
     * @param array       $fields Plain text/number field values, e.g. ['title' => 'X', 'sort_order' => 1].
     *                            To clear an existing file field, set its value to '' here.
     * @param array       $files  Map of field name => $_FILES[...] entry, e.g. ['image' => $_FILES['image']].
     *                            Entries with no file actually chosen (error === UPLOAD_ERR_NO_FILE) are skipped.
     * @param string|null $token  Admin bearer token.
     * @return array|null The saved record, or null on failure.
     */
    /** Set by saveWithFiles()/request() on failure — the actual reason PocketBase rejected the request, if any. */
    public static ?string $lastError = null;

    public static function saveWithFiles(string $collection, ?string $id, array $fields, array $files, ?string $token = null): ?array
    {
        self::$lastError = null;
        $method = $id ? 'PATCH' : 'POST';
        $path   = "/api/collections/$collection/records" . ($id ? "/$id" : '');

        $postFields = $fields;
        foreach ($files as $fieldName => $file) {
            if (!empty($file['tmp_name']) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $postFields[$fieldName] = new CURLFile($file['tmp_name'], $file['type'] ?: 'application/octet-stream', $file['name']);
            }
        }

        $ch = curl_init(rtrim(PB_URL, '/') . $path);
        $headers = [];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $postFields, // array => cURL sends multipart/form-data automatically
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            self::$lastError = 'Connection error: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($status === 200 && is_array($data)) {
            return $data;
        }

        // Surface PocketBase's own explanation (e.g. a field that failed
        // validation) instead of just silently discarding it — this is
        // what previously made a failed save look identical to a
        // successful one in the admin panel.
        self::$lastError = self::describeError($status, $data);
        return null;
    }

    /** Turn a PocketBase error response into one readable line. */
    private static function describeError(int $status, $data): string
    {
        if (!is_array($data)) {
            return "PocketBase returned HTTP $status with no readable error body.";
        }
        $parts = [];
        if (!empty($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $field => $info) {
                $msg = is_array($info) ? ($info['message'] ?? json_encode($info)) : $info;
                $parts[] = "$field: $msg";
            }
        }
        if ($parts) {
            return "HTTP $status — " . implode('; ', $parts);
        }
        return "HTTP $status — " . ($data['message'] ?? 'Save failed for an unknown reason.');
    }

    /**
     * Build the public URL for an uploaded file field, or null if the field is empty.
     *
     * IMPORTANT: this deliberately does NOT point straight at PB_URL. In the
     * Docker Compose setup (and many reverse-proxy setups), PB_URL is an
     * address that's only reachable *from the web server* (e.g.
     * "http://pocketbase:8090", a Docker-internal hostname) — a visitor's
     * browser can never resolve that. Pointing <img> tags at it directly
     * causes every uploaded image to show as broken, no matter how many
     * times an admin re-uploads it.
     *
     * Instead, every file URL goes through this site's own `pb-file.php`,
     * which runs on the web server (so it *can* reach PB_URL) and streams
     * the file back to the browser from the same origin as the website.
     * That works identically whether PocketBase is on 127.0.0.1, a Docker
     * service name, or a private network address.
     */
    public static function fileUrl(array $record, string $field, string $collection): ?string
    {
        if (empty($record[$field])) {
            return null;
        }
        return '/pb-file.php?collection=' . rawurlencode($collection)
            . '&id=' . rawurlencode($record['id'])
            . '&filename=' . rawurlencode($record[$field]);
    }

    /** Authenticate against an auth collection (e.g. 'admins') with identity+password. */
    public static function authWithPassword(string $collection, string $identity, string $password): ?array
    {
        $res = self::request('POST', "/api/collections/$collection/auth-with-password", [
            'identity' => $identity,
            'password' => $password,
        ]);
        return $res['status'] === 200 ? $res['data'] : null;
    }
}
