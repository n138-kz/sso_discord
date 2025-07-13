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
