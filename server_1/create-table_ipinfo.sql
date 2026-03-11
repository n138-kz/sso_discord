--
DROP VIEW IF EXISTS sso_discord_ipinfo_view;
DROP TABLE IF EXISTS sso_discord_ipinfo;
CREATE TABLE IF NOT EXISTS sso_discord_ipinfo (
  "timestamp" double precision NOT NULL DEFAULT EXTRACT(epoch FROM CURRENT_TIMESTAMP),
  ip text NOT NULL UNIQUE,
  hostname text,
  city text,
  region text,
  country text,
  loc text,
  org text,
  postal text,
  timezone text,
  readme text,
  CONSTRAINT sso_discord_ipinfo_pkey PRIMARY KEY (ip)
);
ALTER TABLE IF EXISTS sso_discord_ipinfo OWNER to webapp;
CREATE OR REPLACE VIEW sso_discord_ipinfo_view
  AS
  SELECT
    to_timestamp(trunc(sso_discord_ipinfo."timestamp")) as timestamp,
    sso_discord_ipinfo.ip,
    COALESCE(sso_discord_ipinfo.hostname, sso_discord_ipinfo.ip) as hostname, -- OracleでのNVL、SQL ServerでのISNULL、MySQLでのCOALESCEに相当
    sso_discord_ipinfo.city,
    sso_discord_ipinfo.region,
    sso_discord_ipinfo.country,
    sso_discord_ipinfo.loc,
    sso_discord_ipinfo.org,
    sso_discord_ipinfo.postal,
    sso_discord_ipinfo.timezone,
    sso_discord_ipinfo.readme
  FROM sso_discord_ipinfo
  ORDER BY
    sso_discord_ipinfo."timestamp" DESC;
ALTER VIEW IF EXISTS sso_discord_ipinfo_view OWNER to webapp;
--
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
    to_timestamp(trunc(sso_discord_ipinfo_lite."timestamp")) as timestamp,
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
ALTER VIEW IF EXISTS sso_discord_ipinfo_lite_view OWNER to webapp;
--
DROP VIEW IF EXISTS sso_discord_ipinfo_all_view;
CREATE OR REPLACE VIEW sso_discord_ipinfo_all_view
  AS
  SELECT
    to_timestamp(trunc(EXTRACT(epoch FROM CURRENT_TIMESTAMP))) as timestamp,
    sso_discord_ipinfo.ip,
    COALESCE(sso_discord_ipinfo.hostname, sso_discord_ipinfo.ip) as hostname, -- OracleでのNVL、SQL ServerでのISNULL、MySQLでのCOALESCEに相当
    sso_discord_ipinfo_lite.continent_code,
    sso_discord_ipinfo_lite.continent,
    sso_discord_ipinfo_lite.country_code,
    sso_discord_ipinfo_lite.country,
    sso_discord_ipinfo.postal,
    sso_discord_ipinfo.region,
    sso_discord_ipinfo.city,
    sso_discord_ipinfo.loc,
    sso_discord_ipinfo.timezone,
    sso_discord_ipinfo.org,
    sso_discord_ipinfo.readme,
    sso_discord_ipinfo_lite.asn,
    sso_discord_ipinfo_lite.as_name,
    sso_discord_ipinfo_lite.as_domain
  FROM sso_discord_ipinfo
  FULL OUTER JOIN sso_discord_ipinfo_lite
    ON
    sso_discord_ipinfo.ip = sso_discord_ipinfo_lite.ip
  ORDER BY
    sso_discord_ipinfo.ip DESC;
