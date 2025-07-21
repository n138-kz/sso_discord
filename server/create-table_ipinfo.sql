DROP VIEW IF EXISTS sso_discord_ipinfo_lite_view;
DROP TABLE IF EXISTS sso_discord_ipinfo_lite;
CREATE TABLE IF NOT EXISTS sso_discord_ipinfo_lite (
  "timestamp" double precision NOT NULL DEFAULT EXTRACT(epoch FROM CURRENT_TIMESTAMP),
  ip text NOT NULL UNIQUE,
  asn text,
  as_name text,
  as_domain text,
  country_code text,
  country text,
  continent_code text,
  continent text,
  CONSTRAINT sso_discord_ipinfo_lite_pkey PRIMARY KEY (ip)
);
ALTER TABLE IF EXISTS sso_discord_ipinfo_lite OWNER to webapp;
CREATE OR REPLACE VIEW sso_discord_ipinfo_lite_view
  AS
  SELECT
    to_timestamp(trunc(sso_discord_ipinfo."timestamp")) as timestamp,
    sso_discord_ipinfo_lite.ip,
    sso_discord_ipinfo_lite.asn,
    sso_discord_ipinfo_lite.as_name,
    sso_discord_ipinfo_lite.as_domain,
    sso_discord_ipinfo_lite.country_code,
    sso_discord_ipinfo_lite.country,
    sso_discord_ipinfo_lite.continent_code,
    sso_discord_ipinfo_lite.continent
  FROM sso_discord_ipinfo_lite
  ORDER BY
    sso_discord_ipinfo_lite."timestamp" DESC;
