DROP TABLE IF EXISTS discord_premium_type;
CREATE TABLE IF NOT EXISTS discord_premium_type (
  id int not null unique,
  description text not null,
  CONSTRAINT discord_premium_type_pkey PRIMARY KEY (id)
);
ALTER TABLE IF EXISTS discord_premium_type OWNER to webapp;
Insert into discord_premium_type (id, description) values
(0, 'None'),
(1, 'Nitro Classic'),
(2, 'Nitro'),
(3, 'Nitro Basic');
