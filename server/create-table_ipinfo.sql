DROP TABLE IF EXISTS sso_discord_ipinfo;
CREATE TABLE IF NOT EXISTS sso_discord_ipinfo (
  "timestamp" double precision NOT NULL DEFAULT EXTRACT(epoch FROM CURRENT_TIMESTAMP),
  ip text NOT NULL UNIQUE,
  asn text,
  as_name text,
  as_domain text,
  country_code text,
  country text,
  continent_code text,
  continent text,
  CONSTRAINT sso_discord_ipinfo_pkey PRIMARY KEY (ip)
);
ALTER TABLE IF EXISTS sso_discord_ipinfo OWNER to webapp;
CREATE OR REPLACE VIEW sso_discord_ipinfo_view
  AS
  SELECT
    to_timestamp(trunc(sso_discord_ipinfo."timestamp")) as timestamp,
    sso_discord_ipinfo.ip,
    sso_discord_ipinfo.asn,
    sso_discord_ipinfo.as_name,
    sso_discord_ipinfo.as_domain,
    sso_discord_ipinfo.country_code,
    sso_discord_ipinfo.country,
    sso_discord_ipinfo.continent_code,
    sso_discord_ipinfo.continent
  FROM sso_discord_ipinfo
  ORDER BY
    sso_discord_ipinfo."timestamp" DESC;
