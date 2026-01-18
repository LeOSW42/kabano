--
-- PostgreSQL database dump
--

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry and geography spatial types and functions';


--
-- Name: content_type_enum; Type: TYPE; Schema: public; Owner: kabano
--

CREATE TYPE public.content_type_enum AS ENUM (
    'wiki',
    'blog',
    'forum',
    'poi'
);


ALTER TYPE public.content_type_enum OWNER TO kabano;

--
-- Name: poi_type_enum; Type: TYPE; Schema: public; Owner: kabano
--

CREATE TYPE public.poi_type_enum AS ENUM (
    'basic_hut',
    'wilderness_hut',
    'alpine_hut',
    'halt',
    'bivouac',
    'campsite'
);


ALTER TYPE public.poi_type_enum OWNER TO kabano;

--
-- Name: user_rank_enum; Type: TYPE; Schema: public; Owner: kabano
--

CREATE TYPE public.user_rank_enum AS ENUM (
    'blocked',
    'registered',
    'premium',
    'moderator',
    'administrator',
    'visitor'
);


ALTER TYPE public.user_rank_enum OWNER TO kabano;

--
-- Name: content_comments_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.content_comments_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.content_comments_sequence OWNER TO kabano;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: content_comments; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.content_comments (
    id integer DEFAULT nextval('public.content_comments_sequence'::regclass) NOT NULL,
    version integer,
    creation_date timestamp without time zone,
    update_date timestamp without time zone,
    author integer,
    is_public boolean,
    is_archive boolean,
    content integer,
    comment text,
    locale character varying(32)
);


ALTER TABLE public.content_comments OWNER TO kabano;

--
-- Name: content_contributors_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.content_contributors_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.content_contributors_sequence OWNER TO kabano;

--
-- Name: content_contributors; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.content_contributors (
    id integer DEFAULT nextval('public.content_contributors_sequence'::regclass) NOT NULL,
    content integer,
    contributor integer
);


ALTER TABLE public.content_contributors OWNER TO kabano;

--
-- Name: content_locales_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.content_locales_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.content_locales_sequence OWNER TO kabano;

--
-- Name: content_locales; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.content_locales (
    id integer DEFAULT nextval('public.content_locales_sequence'::regclass) NOT NULL,
    content_id integer NOT NULL,
    locale character varying(32) NOT NULL,
    author integer NOT NULL
);


ALTER TABLE public.content_locales OWNER TO kabano;

--
-- Name: content_version_position; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.content_version_position (
    content_version_id integer NOT NULL,
    geom public.geometry(PointZ,4326) NOT NULL
);


ALTER TABLE public.content_version_position OWNER TO postgres;

--
-- Name: content_versions_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.content_versions_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.content_versions_sequence OWNER TO kabano;

--
-- Name: content_versions; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.content_versions (
    id integer DEFAULT nextval('public.content_versions_sequence'::regclass) NOT NULL,
    version integer DEFAULT 0 NOT NULL,
    update_date timestamp without time zone NOT NULL,
    is_archive boolean DEFAULT false NOT NULL,
    name character varying(255),
    locale_id integer NOT NULL,
    content jsonb,
    CONSTRAINT content_json_is_object CHECK ((jsonb_typeof(content) = 'object'::text))
);


ALTER TABLE public.content_versions OWNER TO kabano;

--
-- Name: contents_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.contents_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contents_sequence OWNER TO kabano;

--
-- Name: contents; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.contents (
    id integer DEFAULT nextval('public.contents_sequence'::regclass) NOT NULL,
    permalink character varying(255) NOT NULL,
    creation_date timestamp without time zone NOT NULL,
    is_public boolean DEFAULT true NOT NULL,
    is_commentable boolean DEFAULT true NOT NULL,
    type public.content_type_enum NOT NULL,
    poi_type public.poi_type_enum,
    CONSTRAINT contents_subtype_required_for_poi CHECK (((type <> 'poi'::public.content_type_enum) OR (poi_type IS NOT NULL)))
);


ALTER TABLE public.contents OWNER TO kabano;

--
-- Name: locales; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.locales (
    name character varying(32) NOT NULL,
    display_name character varying(255) NOT NULL,
    flag_name character varying(32)
);


ALTER TABLE public.locales OWNER TO kabano;

--
-- Name: poi_comments_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_comments_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.poi_comments_sequence OWNER TO kabano;

--
-- Name: poi_contributors_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_contributors_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.poi_contributors_sequence OWNER TO kabano;

--
-- Name: poi_locales_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_locales_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.poi_locales_sequence OWNER TO kabano;

--
-- Name: poi_sources_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_sources_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.poi_sources_sequence OWNER TO kabano;

--
-- Name: poi_versions_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_versions_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.poi_versions_sequence OWNER TO kabano;

--
-- Name: pois_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.pois_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pois_sequence OWNER TO kabano;

--
-- Name: sources; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.sources (
    id character varying(3) NOT NULL,
    display_name character varying(255) NOT NULL,
    icon_name character varying(255),
    website character varying(255),
    license_name character varying(255),
    license_url character varying(255)
);


ALTER TABLE public.sources OWNER TO kabano;

--
-- Name: users_id_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.users_id_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_sequence OWNER TO kabano;

--
-- Name: users; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.users (
    id integer DEFAULT nextval('public.users_id_sequence'::regclass) NOT NULL,
    name character varying(255) NOT NULL,
    version integer DEFAULT 0 NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    website character varying(255),
    is_avatar_present boolean NOT NULL,
    is_archive boolean NOT NULL,
    rank public.user_rank_enum NOT NULL,
    locale character varying(32) NOT NULL,
    timezone character varying(32) NOT NULL,
    visit_date timestamp without time zone NOT NULL,
    register_date timestamp without time zone NOT NULL
);


ALTER TABLE public.users OWNER TO kabano;

--
-- Name: content_comments content_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_pkey PRIMARY KEY (id);


--
-- Name: content_contributors content_contributors_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_pkey PRIMARY KEY (id);


--
-- Name: content_contributors content_contributors_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_unique UNIQUE (content, contributor);


--
-- Name: content_locales content_locales_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_pkey PRIMARY KEY (id);


--
-- Name: content_locales content_locales_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_unique UNIQUE (content_id, locale);


--
-- Name: content_version_position content_version_position_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_version_position
    ADD CONSTRAINT content_version_position_pkey PRIMARY KEY (content_version_id);


--
-- Name: content_versions content_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_versions
    ADD CONSTRAINT content_versions_pkey PRIMARY KEY (id);


--
-- Name: content_versions content_versions_version_locale_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_versions
    ADD CONSTRAINT content_versions_version_locale_key UNIQUE (version, locale_id);


--
-- Name: contents contents_permalink_type_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_permalink_type_key UNIQUE (permalink, type);


--
-- Name: contents contents_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_pkey PRIMARY KEY (id);


--
-- Name: locales locales_display_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_display_name_unique UNIQUE (display_name);


--
-- Name: locales locales_flag_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_flag_name_unique UNIQUE (flag_name);


--
-- Name: locales locales_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_pkey PRIMARY KEY (name);


--
-- Name: sources sources_display_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.sources
    ADD CONSTRAINT sources_display_name_unique UNIQUE (display_name);


--
-- Name: sources sources_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.sources
    ADD CONSTRAINT sources_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_name_unique UNIQUE (name, version);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: content_comments_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX content_comments_is_archive_index ON public.content_comments USING btree (is_archive);


--
-- Name: content_comments_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX content_comments_is_public_index ON public.content_comments USING btree (is_public);


--
-- Name: content_version_position_gix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX content_version_position_gix ON public.content_version_position USING gist (geom);


--
-- Name: contents_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX contents_is_public_index ON public.contents USING btree (is_public);


--
-- Name: fki_content_contributors_content_fkey; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX fki_content_contributors_content_fkey ON public.content_contributors USING btree (content);


--
-- Name: fki_content_contributors_contributor_fkey; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX fki_content_contributors_contributor_fkey ON public.content_contributors USING btree (contributor);


--
-- Name: users_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX users_is_archive_index ON public.users USING btree (is_archive);


--
-- Name: users_register_date_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX users_register_date_index ON public.users USING btree (register_date);


--
-- Name: content_comments content_comments_author_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_author_fkey FOREIGN KEY (author) REFERENCES public.users(id);


--
-- Name: content_comments content_comments_content_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_content_fkey FOREIGN KEY (content) REFERENCES public.contents(id);


--
-- Name: content_comments content_comments_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- Name: content_contributors content_contributors_content_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_content_fkey FOREIGN KEY (content) REFERENCES public.content_locales(id);


--
-- Name: content_contributors content_contributors_contributor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_contributor_fkey FOREIGN KEY (contributor) REFERENCES public.users(id);


--
-- Name: content_locales content_locales_author; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_author FOREIGN KEY (author) REFERENCES public.users(id);


--
-- Name: content_locales content_locales_content; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_content FOREIGN KEY (content_id) REFERENCES public.contents(id);


--
-- Name: content_locales content_locales_locale; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_locale FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- Name: content_version_position content_version_position_content_version_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_version_position
    ADD CONSTRAINT content_version_position_content_version_id_fkey FOREIGN KEY (content_version_id) REFERENCES public.content_versions(id) ON DELETE CASCADE;


--
-- Name: content_versions content_versions_locale; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_versions
    ADD CONSTRAINT content_versions_locale FOREIGN KEY (locale_id) REFERENCES public.content_locales(id);


--
-- Name: users users_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--
