-- Database generated with pgModeler (PostgreSQL Database Modeler).
-- pgModeler  version: 0.9.2-alpha1
-- PostgreSQL version: 11.0
-- Project Site: pgmodeler.io
-- Model Author: ---

-- object: kabano | type: ROLE --
-- DROP ROLE IF EXISTS kabano;
CREATE ROLE kabano WITH 
	INHERIT
	LOGIN
	ENCRYPTED PASSWORD '********';
-- ddl-end --


-- Database creation must be done outside a multicommand file.
-- These commands were put in this file only as a convenience.
-- -- object: kabano | type: DATABASE --
-- -- DROP DATABASE IF EXISTS kabano;
-- CREATE DATABASE kabano
-- 	ENCODING = 'UTF8'
-- 	LC_COLLATE = 'fr_FR.UTF-8'
-- 	LC_CTYPE = 'fr_FR.UTF-8'
-- 	TABLESPACE = pg_default
-- 	OWNER = kabano;
-- -- ddl-end --
-- 

-- object: topology | type: SCHEMA --
-- DROP SCHEMA IF EXISTS topology CASCADE;
CREATE SCHEMA topology;
-- ddl-end --
ALTER SCHEMA topology OWNER TO kabano;
-- ddl-end --
COMMENT ON SCHEMA topology IS 'PostGIS Topology schema';
-- ddl-end --

SET search_path TO pg_catalog,public,topology;
-- ddl-end --

-- object: postgis | type: EXTENSION --
-- DROP EXTENSION IF EXISTS postgis CASCADE;
CREATE EXTENSION postgis
      WITH SCHEMA public
      VERSION '2.5.1';
-- ddl-end --
COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';
-- ddl-end --

-- object: postgis_topology | type: EXTENSION --
-- DROP EXTENSION IF EXISTS postgis_topology CASCADE;
CREATE EXTENSION postgis_topology
      WITH SCHEMA topology
      VERSION '2.5.1';
-- ddl-end --
COMMENT ON EXTENSION postgis_topology IS 'PostGIS topology spatial types and functions';
-- ddl-end --

-- object: public.content_type_enum | type: TYPE --
-- DROP TYPE IF EXISTS public.content_type_enum CASCADE;
CREATE TYPE public.content_type_enum AS
 ENUM ('wiki','blog','forum');
-- ddl-end --
ALTER TYPE public.content_type_enum OWNER TO kabano;
-- ddl-end --

-- object: public.poi_type_enum | type: TYPE --
-- DROP TYPE IF EXISTS public.poi_type_enum CASCADE;
CREATE TYPE public.poi_type_enum AS
 ENUM ('basic_hut','wilderness_hut','alpine_hut','halt','bivouac','campsite');
-- ddl-end --
ALTER TYPE public.poi_type_enum OWNER TO kabano;
-- ddl-end --

-- object: public.user_rank_enum | type: TYPE --
-- DROP TYPE IF EXISTS public.user_rank_enum CASCADE;
CREATE TYPE public.user_rank_enum AS
 ENUM ('blocked','registered','premium','moderator','administrator','visitor');
-- ddl-end --
ALTER TYPE public.user_rank_enum OWNER TO kabano;
-- ddl-end --

-- object: public.content_comments_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.content_comments_sequence CASCADE;
CREATE SEQUENCE public.content_comments_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.content_comments_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.content_comments | type: TABLE --
-- DROP TABLE IF EXISTS public.content_comments CASCADE;
CREATE TABLE public.content_comments (
	id integer NOT NULL DEFAULT nextval('public.content_comments_sequence'::regclass),
	version integer,
	creation_date timestamp,
	update_date timestamp,
	author integer,
	is_public boolean,
	is_archive boolean,
	content integer,
	comment text,
	locale character varying(32),
	CONSTRAINT content_comments_pkey PRIMARY KEY (id)

);
-- ddl-end --
ALTER TABLE public.content_comments OWNER TO kabano;
-- ddl-end --

-- object: public.content_contributors_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.content_contributors_sequence CASCADE;
CREATE SEQUENCE public.content_contributors_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.content_contributors_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.content_contributors | type: TABLE --
-- DROP TABLE IF EXISTS public.content_contributors CASCADE;
CREATE TABLE public.content_contributors (
	id integer NOT NULL DEFAULT nextval('public.content_contributors_sequence'::regclass),
	content integer,
	contributor integer,
	CONSTRAINT content_contributors_pkey PRIMARY KEY (id),
	CONSTRAINT content_contributors_unique UNIQUE (content,contributor)

);
-- ddl-end --
ALTER TABLE public.content_contributors OWNER TO kabano;
-- ddl-end --

-- object: public.contents_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.contents_sequence CASCADE;
CREATE SEQUENCE public.contents_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.contents_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.contents | type: TABLE --
-- DROP TABLE IF EXISTS public.contents CASCADE;
CREATE TABLE public.contents (
	id integer NOT NULL DEFAULT nextval('public.contents_sequence'::regclass),
	permalink character varying(255) NOT NULL,
	creation_date timestamp NOT NULL,
	is_public boolean NOT NULL DEFAULT true,
	is_commentable boolean NOT NULL DEFAULT true,
	type public.content_type_enum NOT NULL,
	CONSTRAINT contents_pkey PRIMARY KEY (id),
	CONSTRAINT contents_permalink_type_key UNIQUE (permalink,type)

);
-- ddl-end --
ALTER TABLE public.contents OWNER TO kabano;
-- ddl-end --

-- object: public.locales | type: TABLE --
-- DROP TABLE IF EXISTS public.locales CASCADE;
CREATE TABLE public.locales (
	name character varying(32) NOT NULL,
	display_name character varying(255) NOT NULL,
	flag_name character varying(32),
	CONSTRAINT locales_display_name_unique UNIQUE (display_name),
	CONSTRAINT locales_flag_name_unique UNIQUE (flag_name),
	CONSTRAINT locales_pkey PRIMARY KEY (name)

);
-- ddl-end --
ALTER TABLE public.locales OWNER TO kabano;
-- ddl-end --

-- object: public.poi_comments_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.poi_comments_sequence CASCADE;
CREATE SEQUENCE public.poi_comments_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.poi_comments_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.poi_comments | type: TABLE --
-- DROP TABLE IF EXISTS public.poi_comments CASCADE;
CREATE TABLE public.poi_comments (
	id integer NOT NULL DEFAULT nextval('public.poi_comments_sequence'::regclass),
	permalink character varying(255),
	version integer,
	creation_date timestamp,
	update_date timestamp,
	author integer,
	is_public boolean,
	is_archive boolean,
	poi integer,
	comment text,
	locale character varying(32),
	CONSTRAINT poi_comments_permalink_version_key UNIQUE (permalink,version),
	CONSTRAINT poi_comments_pkey PRIMARY KEY (id)

);
-- ddl-end --
ALTER TABLE public.poi_comments OWNER TO kabano;
-- ddl-end --

-- object: public.poi_contributors_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.poi_contributors_sequence CASCADE;
CREATE SEQUENCE public.poi_contributors_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.poi_contributors_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.poi_contributors | type: TABLE --
-- DROP TABLE IF EXISTS public.poi_contributors CASCADE;
CREATE TABLE public.poi_contributors (
	id integer NOT NULL DEFAULT nextval('public.poi_contributors_sequence'::regclass),
	poi integer,
	contributor integer,
	CONSTRAINT poi_contributors_pkey PRIMARY KEY (id),
	CONSTRAINT poi_contributors_unique UNIQUE (poi,contributor)

);
-- ddl-end --
ALTER TABLE public.poi_contributors OWNER TO kabano;
-- ddl-end --

-- object: public.poi_locales_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.poi_locales_sequence CASCADE;
CREATE SEQUENCE public.poi_locales_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.poi_locales_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.poi_locales | type: TABLE --
-- DROP TABLE IF EXISTS public.poi_locales CASCADE;
CREATE TABLE public.poi_locales (
	id integer NOT NULL DEFAULT nextval('poi_locales_sequence'::regclass),
	locale character varying(32) NOT NULL,
	poi_id integer NOT NULL,
	CONSTRAINT poi_localised_pkey PRIMARY KEY (id),
	CONSTRAINT poi_localised_unique UNIQUE (locale,poi_id)

);
-- ddl-end --
ALTER TABLE public.poi_locales OWNER TO kabano;
-- ddl-end --

-- object: public.sources | type: TABLE --
-- DROP TABLE IF EXISTS public.sources CASCADE;
CREATE TABLE public.sources (
	id character varying(3) NOT NULL,
	display_name character varying(255) NOT NULL,
	icon_name character varying(255),
	website character varying(255),
	license_name character varying(255),
	license_url character varying(255),
	CONSTRAINT sources_display_name_unique UNIQUE (display_name),
	CONSTRAINT sources_pkey PRIMARY KEY (id)

);
-- ddl-end --
ALTER TABLE public.sources OWNER TO kabano;
-- ddl-end --

-- object: public.poi_versions_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.poi_versions_sequence CASCADE;
CREATE SEQUENCE public.poi_versions_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.poi_versions_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.pois_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.pois_sequence CASCADE;
CREATE SEQUENCE public.pois_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.pois_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.users_id_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.users_id_sequence CASCADE;
CREATE SEQUENCE public.users_id_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.users_id_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.users | type: TABLE --
-- DROP TABLE IF EXISTS public.users CASCADE;
CREATE TABLE public.users (
	id integer NOT NULL DEFAULT nextval('public.users_id_sequence'::regclass),
	name character varying(255) NOT NULL,
	version integer NOT NULL DEFAULT 0,
	email character varying(255) NOT NULL,
	password character varying(255) NOT NULL,
	website character varying(255),
	is_avatar_present boolean NOT NULL,
	is_archive boolean NOT NULL,
	rank public.user_rank_enum NOT NULL,
	locale character varying(32) NOT NULL,
	timezone character varying(8) NOT NULL,
	visit_date timestamp NOT NULL,
	register_date timestamp NOT NULL,
	CONSTRAINT users_email_unique UNIQUE (email),
	CONSTRAINT users_name_unique UNIQUE (name,version),
	CONSTRAINT users_pkey PRIMARY KEY (id)

);
-- ddl-end --
ALTER TABLE public.users OWNER TO kabano;
-- ddl-end --

-- object: content_comments_is_archive_index | type: INDEX --
-- DROP INDEX IF EXISTS public.content_comments_is_archive_index CASCADE;
CREATE INDEX content_comments_is_archive_index ON public.content_comments
	USING btree
	(
	  is_archive
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: content_comments_is_public_index | type: INDEX --
-- DROP INDEX IF EXISTS public.content_comments_is_public_index CASCADE;
CREATE INDEX content_comments_is_public_index ON public.content_comments
	USING btree
	(
	  is_public
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: contents_is_public_index | type: INDEX --
-- DROP INDEX IF EXISTS public.contents_is_public_index CASCADE;
CREATE INDEX contents_is_public_index ON public.contents
	USING btree
	(
	  is_public
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: poi_comments_is_archive_index | type: INDEX --
-- DROP INDEX IF EXISTS public.poi_comments_is_archive_index CASCADE;
CREATE INDEX poi_comments_is_archive_index ON public.poi_comments
	USING btree
	(
	  is_archive
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: poi_comments_is_public_index | type: INDEX --
-- DROP INDEX IF EXISTS public.poi_comments_is_public_index CASCADE;
CREATE INDEX poi_comments_is_public_index ON public.poi_comments
	USING btree
	(
	  is_public
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: users_is_archive_index | type: INDEX --
-- DROP INDEX IF EXISTS public.users_is_archive_index CASCADE;
CREATE INDEX users_is_archive_index ON public.users
	USING btree
	(
	  is_archive
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: users_register_date_index | type: INDEX --
-- DROP INDEX IF EXISTS public.users_register_date_index CASCADE;
CREATE INDEX users_register_date_index ON public.users
	USING btree
	(
	  register_date
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: public.content_locales_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.content_locales_sequence CASCADE;
CREATE SEQUENCE public.content_locales_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.content_locales_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.content_versions_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.content_versions_sequence CASCADE;
CREATE SEQUENCE public.content_versions_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.content_versions_sequence OWNER TO kabano;
-- ddl-end --

-- object: fki_content_contributors_contributor_fkey | type: INDEX --
-- DROP INDEX IF EXISTS public.fki_content_contributors_contributor_fkey CASCADE;
CREATE INDEX fki_content_contributors_contributor_fkey ON public.content_contributors
	USING btree
	(
	  contributor
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: fki_content_contributors_content_fkey | type: INDEX --
-- DROP INDEX IF EXISTS public.fki_content_contributors_content_fkey CASCADE;
CREATE INDEX fki_content_contributors_content_fkey ON public.content_contributors
	USING btree
	(
	  content
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: public.content_locales | type: TABLE --
-- DROP TABLE IF EXISTS public.content_locales CASCADE;
CREATE TABLE public.content_locales (
	id integer NOT NULL DEFAULT nextval('public.content_locales_sequence'::regclass),
	content_id integer NOT NULL,
	locale character varying(32) NOT NULL,
	author integer NOT NULL,
	CONSTRAINT content_locales_pkey PRIMARY KEY (id),
	CONSTRAINT content_locales_unique UNIQUE (content_id,locale)

);
-- ddl-end --
ALTER TABLE public.content_locales OWNER TO kabano;
-- ddl-end --

-- object: public.content_versions | type: TABLE --
-- DROP TABLE IF EXISTS public.content_versions CASCADE;
CREATE TABLE public.content_versions (
	id integer NOT NULL DEFAULT nextval('public.content_versions_sequence'::regclass),
	version integer NOT NULL DEFAULT 0,
	update_date timestamp NOT NULL,
	is_archive boolean NOT NULL DEFAULT false,
	name character varying(255),
	content text,
	locale_id integer NOT NULL,
	CONSTRAINT content_versions_pkey PRIMARY KEY (id),
	CONSTRAINT content_versions_version_locale_key UNIQUE (version,locale_id)

);
-- ddl-end --
ALTER TABLE public.content_versions OWNER TO kabano;
-- ddl-end --

-- object: public.pois | type: TABLE --
-- DROP TABLE IF EXISTS public.pois CASCADE;
CREATE TABLE public.pois (
	id integer NOT NULL DEFAULT nextval('public.poi_versions_sequence'::regclass),
	is_public boolean NOT NULL DEFAULT true,
	permalink character varying(255) NOT NULL,
	creation_date timestamp NOT NULL,
	name character varying(255) NOT NULL,
	"position" geometry NOT NULL,
	type public.poi_type_enum NOT NULL,
	CONSTRAINT pois_pkey PRIMARY KEY (id),
	CONSTRAINT pois_permalink_key UNIQUE (permalink),
	CONSTRAINT pois_position_type_key UNIQUE ("position",type)

);
-- ddl-end --
ALTER TABLE public.pois OWNER TO kabano;
-- ddl-end --

-- object: public.poi_versions | type: TABLE --
-- DROP TABLE IF EXISTS public.poi_versions CASCADE;
CREATE TABLE public.poi_versions (
	id integer NOT NULL DEFAULT nextval('public.pois_sequence'::regclass),
	version integer NOT NULL DEFAULT 0,
	update_date timestamp NOT NULL,
	is_archive boolean NOT NULL DEFAULT false,
	alt_type public.poi_type_enum NOT NULL,
	is_destroyed boolean NOT NULL DEFAULT false,
	alt_name character varying(255),
	alt_position geometry NOT NULL,
	parameters jsonb,
	source_id integer NOT NULL,
	CONSTRAINT poi_versions_pkey PRIMARY KEY (id),
	CONSTRAINT poi_versions_version_source_key UNIQUE (version,source_id)

);
-- ddl-end --
ALTER TABLE public.poi_versions OWNER TO kabano;
-- ddl-end --

-- object: poi_versions_is_archive_index | type: INDEX --
-- DROP INDEX IF EXISTS public.poi_versions_is_archive_index CASCADE;
CREATE INDEX poi_versions_is_archive_index ON public.poi_versions
	USING btree
	(
	  is_archive
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: poi_versions_is_destroyed_index | type: INDEX --
-- DROP INDEX IF EXISTS public.poi_versions_is_destroyed_index CASCADE;
CREATE INDEX poi_versions_is_destroyed_index ON public.poi_versions
	USING btree
	(
	  is_destroyed
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: poi_versions_type_index | type: INDEX --
-- DROP INDEX IF EXISTS public.poi_versions_type_index CASCADE;
CREATE INDEX poi_versions_type_index ON public.poi_versions
	USING btree
	(
	  alt_type
	)
	WITH (FILLFACTOR = 90);
-- ddl-end --

-- object: public.poi_sources_sequence | type: SEQUENCE --
-- DROP SEQUENCE IF EXISTS public.poi_sources_sequence CASCADE;
CREATE SEQUENCE public.poi_sources_sequence
	INCREMENT BY 1
	MINVALUE 1
	MAXVALUE 9223372036854775807
	START WITH 1
	CACHE 1
	NO CYCLE
	OWNED BY NONE;
-- ddl-end --
ALTER SEQUENCE public.poi_sources_sequence OWNER TO kabano;
-- ddl-end --

-- object: public.poi_sources | type: TABLE --
-- DROP TABLE IF EXISTS public.poi_sources CASCADE;
CREATE TABLE public.poi_sources (
	id integer NOT NULL DEFAULT nextval('public.poi_sources_sequence'::regclass),
	source character varying(3),
	remote_source_id character varying(255),
	author integer NOT NULL,
	locale_id integer NOT NULL,
	CONSTRAINT poi_sources_pkey PRIMARY KEY (id),
	CONSTRAINT poi_sources_source_key UNIQUE (source,remote_source_id),
	CONSTRAINT poi_sources_locale_key UNIQUE (locale_id,source)

);
-- ddl-end --
ALTER TABLE public.poi_sources OWNER TO kabano;
-- ddl-end --

-- object: content_comments_author_fkey | type: CONSTRAINT --
-- ALTER TABLE public.content_comments DROP CONSTRAINT IF EXISTS content_comments_author_fkey CASCADE;
ALTER TABLE public.content_comments ADD CONSTRAINT content_comments_author_fkey FOREIGN KEY (author)
REFERENCES public.users (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: content_comments_content_fkey | type: CONSTRAINT --
-- ALTER TABLE public.content_comments DROP CONSTRAINT IF EXISTS content_comments_content_fkey CASCADE;
ALTER TABLE public.content_comments ADD CONSTRAINT content_comments_content_fkey FOREIGN KEY (content)
REFERENCES public.contents (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: content_comments_locale_fkey | type: CONSTRAINT --
-- ALTER TABLE public.content_comments DROP CONSTRAINT IF EXISTS content_comments_locale_fkey CASCADE;
ALTER TABLE public.content_comments ADD CONSTRAINT content_comments_locale_fkey FOREIGN KEY (locale)
REFERENCES public.locales (name) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: content_contributors_contributor_fkey | type: CONSTRAINT --
-- ALTER TABLE public.content_contributors DROP CONSTRAINT IF EXISTS content_contributors_contributor_fkey CASCADE;
ALTER TABLE public.content_contributors ADD CONSTRAINT content_contributors_contributor_fkey FOREIGN KEY (contributor)
REFERENCES public.users (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: content_contributors_content_fkey | type: CONSTRAINT --
-- ALTER TABLE public.content_contributors DROP CONSTRAINT IF EXISTS content_contributors_content_fkey CASCADE;
ALTER TABLE public.content_contributors ADD CONSTRAINT content_contributors_content_fkey FOREIGN KEY (content)
REFERENCES public.content_locales (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_comments_author_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_comments DROP CONSTRAINT IF EXISTS poi_comments_author_fkey CASCADE;
ALTER TABLE public.poi_comments ADD CONSTRAINT poi_comments_author_fkey FOREIGN KEY (author)
REFERENCES public.users (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_comments_locale_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_comments DROP CONSTRAINT IF EXISTS poi_comments_locale_fkey CASCADE;
ALTER TABLE public.poi_comments ADD CONSTRAINT poi_comments_locale_fkey FOREIGN KEY (locale)
REFERENCES public.locales (name) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_comments_poi_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_comments DROP CONSTRAINT IF EXISTS poi_comments_poi_fkey CASCADE;
ALTER TABLE public.poi_comments ADD CONSTRAINT poi_comments_poi_fkey FOREIGN KEY (poi)
REFERENCES public.poi_sources (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_contributors_contributor_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_contributors DROP CONSTRAINT IF EXISTS poi_contributors_contributor_fkey CASCADE;
ALTER TABLE public.poi_contributors ADD CONSTRAINT poi_contributors_contributor_fkey FOREIGN KEY (contributor)
REFERENCES public.users (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_contributors_poi_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_contributors DROP CONSTRAINT IF EXISTS poi_contributors_poi_fkey CASCADE;
ALTER TABLE public.poi_contributors ADD CONSTRAINT poi_contributors_poi_fkey FOREIGN KEY (poi)
REFERENCES public.poi_sources (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_localised_locale_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_locales DROP CONSTRAINT IF EXISTS poi_localised_locale_fkey CASCADE;
ALTER TABLE public.poi_locales ADD CONSTRAINT poi_localised_locale_fkey FOREIGN KEY (locale)
REFERENCES public.locales (name) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_locales_poi_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_locales DROP CONSTRAINT IF EXISTS poi_locales_poi_fkey CASCADE;
ALTER TABLE public.poi_locales ADD CONSTRAINT poi_locales_poi_fkey FOREIGN KEY (poi_id)
REFERENCES public.pois (id) MATCH FULL
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: users_locale_fkey | type: CONSTRAINT --
-- ALTER TABLE public.users DROP CONSTRAINT IF EXISTS users_locale_fkey CASCADE;
ALTER TABLE public.users ADD CONSTRAINT users_locale_fkey FOREIGN KEY (locale)
REFERENCES public.locales (name) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: content_locales_locale | type: CONSTRAINT --
-- ALTER TABLE public.content_locales DROP CONSTRAINT IF EXISTS content_locales_locale CASCADE;
ALTER TABLE public.content_locales ADD CONSTRAINT content_locales_locale FOREIGN KEY (locale)
REFERENCES public.locales (name) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: content_locales_content | type: CONSTRAINT --
-- ALTER TABLE public.content_locales DROP CONSTRAINT IF EXISTS content_locales_content CASCADE;
ALTER TABLE public.content_locales ADD CONSTRAINT content_locales_content FOREIGN KEY (content_id)
REFERENCES public.contents (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: content_locales_author | type: CONSTRAINT --
-- ALTER TABLE public.content_locales DROP CONSTRAINT IF EXISTS content_locales_author CASCADE;
ALTER TABLE public.content_locales ADD CONSTRAINT content_locales_author FOREIGN KEY (author)
REFERENCES public.users (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: content_versions_locale | type: CONSTRAINT --
-- ALTER TABLE public.content_versions DROP CONSTRAINT IF EXISTS content_versions_locale CASCADE;
ALTER TABLE public.content_versions ADD CONSTRAINT content_versions_locale FOREIGN KEY (locale_id)
REFERENCES public.content_locales (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_versions_source_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_versions DROP CONSTRAINT IF EXISTS poi_versions_source_fkey CASCADE;
ALTER TABLE public.poi_versions ADD CONSTRAINT poi_versions_source_fkey FOREIGN KEY (source_id)
REFERENCES public.poi_sources (id) MATCH SIMPLE
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_sources_locale_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_sources DROP CONSTRAINT IF EXISTS poi_sources_locale_fkey CASCADE;
ALTER TABLE public.poi_sources ADD CONSTRAINT poi_sources_locale_fkey FOREIGN KEY (source)
REFERENCES public.sources (id) MATCH FULL
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --

-- object: poi_sources_poi_locale_fkey | type: CONSTRAINT --
-- ALTER TABLE public.poi_sources DROP CONSTRAINT IF EXISTS poi_sources_poi_locale_fkey CASCADE;
ALTER TABLE public.poi_sources ADD CONSTRAINT poi_sources_poi_locale_fkey FOREIGN KEY (locale_id)
REFERENCES public.poi_locales (id) MATCH FULL
ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ddl-end --


