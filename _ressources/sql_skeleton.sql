--
-- PostgreSQL database cluster dump
--

-- Started on 2026-01-18 13:24:51 CET

\restrict zBsV2i7KJ2Icq3HtbOeWFsgcJfohHtzREyc9ZWIzfgOdQcgw5ViINwJuAFQk0WY

SET default_transaction_read_only = off;

SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;

--
-- Roles
--

CREATE ROLE kabano;
ALTER ROLE kabano WITH NOSUPERUSER INHERIT CREATEROLE CREATEDB LOGIN NOREPLICATION NOBYPASSRLS;
CREATE ROLE postgres;
ALTER ROLE postgres WITH SUPERUSER INHERIT CREATEROLE CREATEDB LOGIN REPLICATION BYPASSRLS;

--
-- User Configurations
--








\unrestrict zBsV2i7KJ2Icq3HtbOeWFsgcJfohHtzREyc9ZWIzfgOdQcgw5ViINwJuAFQk0WY

--
-- Databases
--

--
-- Database "template1" dump
--

\connect template1

--
-- PostgreSQL database dump
--

\restrict N4deUMF6T8wvIbcGzzGoslc1W6Ko7vIwj6D9iFxaEPdDC4n9DIcZ0MJIcNN0edI

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

-- Started on 2026-01-18 13:24:51 CET

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

-- Completed on 2026-01-18 13:24:51 CET

--
-- PostgreSQL database dump complete
--

\unrestrict N4deUMF6T8wvIbcGzzGoslc1W6Ko7vIwj6D9iFxaEPdDC4n9DIcZ0MJIcNN0edI

--
-- Database "kabano" dump
--

--
-- PostgreSQL database dump
--

\restrict bSLw7Pr4TIr8dQjZ18Ar1YjCDA44ZzM9Sih1yiKYfw4huFAwIekfsvVnECWSWT0

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

-- Started on 2026-01-18 13:24:51 CET

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
-- TOC entry 4519 (class 1262 OID 16389)
-- Name: kabano; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE kabano WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'fr_FR.UTF-8';


ALTER DATABASE kabano OWNER TO postgres;

\unrestrict bSLw7Pr4TIr8dQjZ18Ar1YjCDA44ZzM9Sih1yiKYfw4huFAwIekfsvVnECWSWT0
\connect kabano
\restrict bSLw7Pr4TIr8dQjZ18Ar1YjCDA44ZzM9Sih1yiKYfw4huFAwIekfsvVnECWSWT0

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
-- TOC entry 6 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- TOC entry 2 (class 3079 OID 16816)
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- TOC entry 4521 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry and geography spatial types and functions';


--
-- TOC entry 1632 (class 1247 OID 16391)
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
-- TOC entry 1635 (class 1247 OID 16398)
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
-- TOC entry 1638 (class 1247 OID 16412)
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
-- TOC entry 220 (class 1259 OID 16425)
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
-- TOC entry 221 (class 1259 OID 16426)
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
-- TOC entry 222 (class 1259 OID 16432)
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
-- TOC entry 223 (class 1259 OID 16433)
-- Name: content_contributors; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.content_contributors (
    id integer DEFAULT nextval('public.content_contributors_sequence'::regclass) NOT NULL,
    content integer,
    contributor integer
);


ALTER TABLE public.content_contributors OWNER TO kabano;

--
-- TOC entry 224 (class 1259 OID 16437)
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
-- TOC entry 225 (class 1259 OID 16438)
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
-- TOC entry 245 (class 1259 OID 17898)
-- Name: content_version_position; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.content_version_position (
    content_version_id integer NOT NULL,
    geom public.geometry(PointZ,4326) NOT NULL
);


ALTER TABLE public.content_version_position OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 16442)
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
-- TOC entry 227 (class 1259 OID 16443)
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
-- TOC entry 228 (class 1259 OID 16451)
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
-- TOC entry 229 (class 1259 OID 16452)
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
-- TOC entry 230 (class 1259 OID 16458)
-- Name: locales; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.locales (
    name character varying(32) NOT NULL,
    display_name character varying(255) NOT NULL,
    flag_name character varying(32)
);


ALTER TABLE public.locales OWNER TO kabano;

--
-- TOC entry 231 (class 1259 OID 16461)
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
-- TOC entry 232 (class 1259 OID 16468)
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
-- TOC entry 233 (class 1259 OID 16473)
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
-- TOC entry 234 (class 1259 OID 16478)
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
-- TOC entry 235 (class 1259 OID 16483)
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
-- TOC entry 236 (class 1259 OID 16484)
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
-- TOC entry 237 (class 1259 OID 16485)
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
-- TOC entry 238 (class 1259 OID 16490)
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
-- TOC entry 239 (class 1259 OID 16491)
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
-- TOC entry 4308 (class 2606 OID 16457)
-- Name: content_comments content_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_pkey PRIMARY KEY (id);


--
-- TOC entry 4310 (class 2606 OID 16458)
-- Name: content_contributors content_contributors_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_pkey PRIMARY KEY (id);


--
-- TOC entry 4312 (class 2606 OID 16459)
-- Name: content_contributors content_contributors_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_unique UNIQUE (content, contributor);


--
-- TOC entry 4316 (class 2606 OID 16460)
-- Name: content_locales content_locales_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_pkey PRIMARY KEY (id);


--
-- TOC entry 4318 (class 2606 OID 16461)
-- Name: content_locales content_locales_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_unique UNIQUE (content_id, locale);


--
-- TOC entry 4350 (class 2606 OID 17906)
-- Name: content_version_position content_version_position_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_version_position
    ADD CONSTRAINT content_version_position_pkey PRIMARY KEY (content_version_id);


--
-- TOC entry 4320 (class 2606 OID 16462)
-- Name: content_versions content_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_versions
    ADD CONSTRAINT content_versions_pkey PRIMARY KEY (id);


--
-- TOC entry 4322 (class 2606 OID 16463)
-- Name: content_versions content_versions_version_locale_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_versions
    ADD CONSTRAINT content_versions_version_locale_key UNIQUE (version, locale_id);


--
-- TOC entry 4325 (class 2606 OID 16464)
-- Name: contents contents_permalink_type_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_permalink_type_key UNIQUE (permalink, type);


--
-- TOC entry 4327 (class 2606 OID 16465)
-- Name: contents contents_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_pkey PRIMARY KEY (id);


--
-- TOC entry 4329 (class 2606 OID 16466)
-- Name: locales locales_display_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_display_name_unique UNIQUE (display_name);


--
-- TOC entry 4331 (class 2606 OID 16467)
-- Name: locales locales_flag_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_flag_name_unique UNIQUE (flag_name);


--
-- TOC entry 4333 (class 2606 OID 16468)
-- Name: locales locales_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_pkey PRIMARY KEY (name);


--
-- TOC entry 4335 (class 2606 OID 16478)
-- Name: sources sources_display_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.sources
    ADD CONSTRAINT sources_display_name_unique UNIQUE (display_name);


--
-- TOC entry 4337 (class 2606 OID 16479)
-- Name: sources sources_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.sources
    ADD CONSTRAINT sources_pkey PRIMARY KEY (id);


--
-- TOC entry 4339 (class 2606 OID 16480)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 4342 (class 2606 OID 16481)
-- Name: users users_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_name_unique UNIQUE (name, version);


--
-- TOC entry 4344 (class 2606 OID 16482)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 4305 (class 1259 OID 16550)
-- Name: content_comments_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX content_comments_is_archive_index ON public.content_comments USING btree (is_archive);


--
-- TOC entry 4306 (class 1259 OID 16551)
-- Name: content_comments_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX content_comments_is_public_index ON public.content_comments USING btree (is_public);


--
-- TOC entry 4348 (class 1259 OID 17918)
-- Name: content_version_position_gix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX content_version_position_gix ON public.content_version_position USING gist (geom);


--
-- TOC entry 4323 (class 1259 OID 16552)
-- Name: contents_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX contents_is_public_index ON public.contents USING btree (is_public);


--
-- TOC entry 4313 (class 1259 OID 16553)
-- Name: fki_content_contributors_content_fkey; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX fki_content_contributors_content_fkey ON public.content_contributors USING btree (content);


--
-- TOC entry 4314 (class 1259 OID 16554)
-- Name: fki_content_contributors_contributor_fkey; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX fki_content_contributors_contributor_fkey ON public.content_contributors USING btree (contributor);


--
-- TOC entry 4340 (class 1259 OID 16557)
-- Name: users_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX users_is_archive_index ON public.users USING btree (is_archive);


--
-- TOC entry 4345 (class 1259 OID 16558)
-- Name: users_register_date_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX users_register_date_index ON public.users USING btree (register_date);


--
-- TOC entry 4351 (class 2606 OID 16483)
-- Name: content_comments content_comments_author_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_author_fkey FOREIGN KEY (author) REFERENCES public.users(id);


--
-- TOC entry 4352 (class 2606 OID 16488)
-- Name: content_comments content_comments_content_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_content_fkey FOREIGN KEY (content) REFERENCES public.contents(id);


--
-- TOC entry 4353 (class 2606 OID 16493)
-- Name: content_comments content_comments_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- TOC entry 4354 (class 2606 OID 16498)
-- Name: content_contributors content_contributors_content_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_content_fkey FOREIGN KEY (content) REFERENCES public.content_locales(id);


--
-- TOC entry 4355 (class 2606 OID 16503)
-- Name: content_contributors content_contributors_contributor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_contributor_fkey FOREIGN KEY (contributor) REFERENCES public.users(id);


--
-- TOC entry 4356 (class 2606 OID 16508)
-- Name: content_locales content_locales_author; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_author FOREIGN KEY (author) REFERENCES public.users(id);


--
-- TOC entry 4357 (class 2606 OID 16513)
-- Name: content_locales content_locales_content; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_content FOREIGN KEY (content_id) REFERENCES public.contents(id);


--
-- TOC entry 4358 (class 2606 OID 16518)
-- Name: content_locales content_locales_locale; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_locales
    ADD CONSTRAINT content_locales_locale FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- TOC entry 4361 (class 2606 OID 17907)
-- Name: content_version_position content_version_position_content_version_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_version_position
    ADD CONSTRAINT content_version_position_content_version_id_fkey FOREIGN KEY (content_version_id) REFERENCES public.content_versions(id) ON DELETE CASCADE;


--
-- TOC entry 4359 (class 2606 OID 16523)
-- Name: content_versions content_versions_locale; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_versions
    ADD CONSTRAINT content_versions_locale FOREIGN KEY (locale_id) REFERENCES public.content_locales(id);


--
-- TOC entry 4360 (class 2606 OID 16568)
-- Name: users users_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- TOC entry 4520 (class 0 OID 0)
-- Dependencies: 6
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2026-01-18 13:24:51 CET

--
-- PostgreSQL database dump complete
--

\unrestrict bSLw7Pr4TIr8dQjZ18Ar1YjCDA44ZzM9Sih1yiKYfw4huFAwIekfsvVnECWSWT0

-- Completed on 2026-01-18 13:24:51 CET

--
-- PostgreSQL database cluster dump complete
--

