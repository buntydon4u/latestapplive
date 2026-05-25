-- Optional table for production logout/token revocation.
-- The API works without this table, but logout becomes client-side only.

CREATE TABLE IF NOT EXISTS api_token_blacklist (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_api_token_blacklist_token_hash (token_hash),
    KEY idx_api_token_blacklist_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
