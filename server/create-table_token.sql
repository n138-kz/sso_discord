DROP TABLE IF EXISTS sso_discord_token;
CREATE TABLE IF NOT EXISTS sso_discord_token (
  "timestamp" double precision NOT NULL DEFAULT EXTRACT(epoch FROM CURRENT_TIMESTAMP),
  userid text NOT NULL,
  access_code text NOT NULL,
  access_token text NOT NULL,
  expires_in int DEFAULT 0,
  refresh_token text NOT NULL,
  scope text NOT NULL,
  token_type text NOT NULL,
  revoked boolean NOT NULL DEFAULT false,
  CONSTRAINT sso_discord_token_pkey PRIMARY KEY (access_code)
);
ALTER TABLE IF EXISTS sso_discord_token OWNER to webapp;
