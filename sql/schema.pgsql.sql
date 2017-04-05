CREATE TABLE fleet_device (
  id serial,
  type integer,
  number integer,
  mac character varying,
  imei bigint,
  imsi bigint,
  nsce bigint,
  purchased date,
  created timestamp with time zone,
  updated timestamp with time zone,
  price double precision,
  status character varying,
  comments character varying,
  PRIMARY KEY (id)
);

CREATE TABLE fleet_device_type (
  id serial,
  manufacturer character varying,
  name character varying,
  type character varying,
  PRIMARY KEY (id)
);

CREATE TABLE fleet_lending (
  id serial,
  device integer,
  started timestamp with time zone,
  ended timestamp with time zone,
  status character varying,
  segment character varying,
  comments character varying,
  first_name character varying,
  last_name character varying,
  email character varying,
  phone character varying,
  token character varying unique,
  PRIMARY KEY (id)
);
