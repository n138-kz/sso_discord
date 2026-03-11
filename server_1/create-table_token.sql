DROP VIEW IF EXISTS sso_discord_token_view;
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
  remote_address text,
  FOREIGN KEY(remote_address) REFERENCES sso_discord_ipinfo(ip),
  CONSTRAINT sso_discord_token_pkey PRIMARY KEY (access_token)
);
ALTER TABLE IF EXISTS sso_discord_token OWNER to webapp;
CREATE OR REPLACE VIEW sso_discord_token_view
  AS
  SELECT
    to_timestamp(trunc(sso_discord_token."timestamp")) as timestamp,
    sso_discord_token.userid,
    sso_discord_discordme.username,
    sso_discord_discordme.global_name,
    sso_discord_token.remote_address,
    sso_discord_token.revoked,
    sso_discord_token.access_code,
    sso_discord_token.access_token,
    sso_discord_token.expires_in, -- unit: seconds
    sso_discord_token.refresh_token,
    sso_discord_token.scope,
    sso_discord_token.token_type
  FROM sso_discord_token
  INNER JOIN sso_discord_discordme
	ON
    sso_discord_token.userid = sso_discord_discordme.userid
  ORDER BY
    sso_discord_token."timestamp" DESC;
ALTER TABLE IF EXISTS sso_discord_token_view OWNER to webapp;
