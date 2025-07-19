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
