DROP VIEW IF EXISTS sso_discord_discordme_view;
DROP TABLE IF EXISTS sso_discord_discordme;
CREATE TABLE IF NOT EXISTS sso_discord_discordme (
  "timestamp" double precision NOT NULL DEFAULT EXTRACT(epoch FROM CURRENT_TIMESTAMP),
  userid text NOT NULL,
  username text,
  global_name text,
  avatar text,
  discriminator bigint,
  public_flags bigint,
  flags bigint,
  banner text,
  accent_color bigint,
  avatar_decoration_data text,
  collectibles text,
  banner_color text,
  clan json,
  primary_guild json,
  locale text,
  premium_type int,
  CONSTRAINT sso_discord_discordme_pkey PRIMARY KEY (userid)
);
ALTER TABLE IF EXISTS sso_discord_discordme OWNER to webapp;
ALTER TABLE IF EXISTS sso_discord_discordme
  ADD CONSTRAINT sso_discord_discordme_premium_type_fkey FOREIGN KEY (premium_type)
  REFERENCES discord_premium_type (id) MATCH SIMPLE;
CREATE OR REPLACE VIEW sso_discord_discordme_view
  AS
  SELECT
    to_timestamp(trunc(sso_discord_discordme."timestamp")) as timestamp,
    sso_discord_discordme.userid,
    sso_discord_discordme.username,
    sso_discord_discordme.global_name,
    sso_discord_discordme.avatar,
    sso_discord_discordme.discriminator,
    sso_discord_discordme.public_flags,
    sso_discord_discordme.flags,
    sso_discord_discordme.banner,
    sso_discord_discordme.accent_color,
    sso_discord_discordme.avatar_decoration_data,
    sso_discord_discordme.collectibles,
    sso_discord_discordme.banner_color,
    sso_discord_discordme.clan,
    sso_discord_discordme.primary_guild,
    sso_discord_discordme.locale,
    discord_premium_type.description as premium_type
  FROM sso_discord_discordme
  INNER JOIN discord_premium_type
	ON
    sso_discord_discordme.premium_type = discord_premium_type.id
  ORDER BY
    sso_discord_discordme."timestamp" DESC;
