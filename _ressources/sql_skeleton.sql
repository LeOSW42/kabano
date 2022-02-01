--
-- PostgreSQL database dump
--

-- Dumped from database version 13.4
-- Dumped by pg_dump version 13.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: topology; Type: SCHEMA; Schema: -; Owner: kabano
--

CREATE SCHEMA topology;


ALTER SCHEMA topology OWNER TO kabano;

--
-- Name: SCHEMA topology; Type: COMMENT; Schema: -; Owner: kabano
--

COMMENT ON SCHEMA topology IS 'PostGIS Topology schema';


--
-- Name: content_type_enum; Type: TYPE; Schema: public; Owner: kabano
--

CREATE TYPE public.content_type_enum AS ENUM (
    'wiki',
    'blog',
    'forum'
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


ALTER TABLE public.content_comments_sequence OWNER TO kabano;

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


ALTER TABLE public.content_contributors_sequence OWNER TO kabano;

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


ALTER TABLE public.content_locales_sequence OWNER TO kabano;

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
-- Name: content_versions_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.content_versions_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.content_versions_sequence OWNER TO kabano;

--
-- Name: content_versions; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.content_versions (
    id integer DEFAULT nextval('public.content_versions_sequence'::regclass) NOT NULL,
    version integer DEFAULT 0 NOT NULL,
    update_date timestamp without time zone NOT NULL,
    is_archive boolean DEFAULT false NOT NULL,
    name character varying(255),
    content text,
    locale_id integer NOT NULL
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


ALTER TABLE public.contents_sequence OWNER TO kabano;

--
-- Name: contents; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.contents (
    id integer DEFAULT nextval('public.contents_sequence'::regclass) NOT NULL,
    permalink character varying(255) NOT NULL,
    creation_date timestamp without time zone NOT NULL,
    is_public boolean DEFAULT true NOT NULL,
    is_commentable boolean DEFAULT true NOT NULL,
    type public.content_type_enum NOT NULL
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


ALTER TABLE public.poi_comments_sequence OWNER TO kabano;

--
-- Name: poi_comments; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.poi_comments (
    id integer DEFAULT nextval('public.poi_comments_sequence'::regclass) NOT NULL,
    permalink character varying(255),
    version integer,
    creation_date timestamp without time zone,
    update_date timestamp without time zone,
    author integer,
    is_public boolean,
    is_archive boolean,
    poi integer,
    comment text,
    locale character varying(32)
);


ALTER TABLE public.poi_comments OWNER TO kabano;

--
-- Name: poi_contributors_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_contributors_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.poi_contributors_sequence OWNER TO kabano;

--
-- Name: poi_contributors; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.poi_contributors (
    id integer DEFAULT nextval('public.poi_contributors_sequence'::regclass) NOT NULL,
    poi integer,
    contributor integer
);


ALTER TABLE public.poi_contributors OWNER TO kabano;

--
-- Name: poi_locales_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_locales_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.poi_locales_sequence OWNER TO kabano;

--
-- Name: poi_locales; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.poi_locales (
    id integer DEFAULT nextval('public.poi_locales_sequence'::regclass) NOT NULL,
    locale character varying(32) NOT NULL,
    poi_id integer NOT NULL
);


ALTER TABLE public.poi_locales OWNER TO kabano;

--
-- Name: poi_sources_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_sources_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.poi_sources_sequence OWNER TO kabano;

--
-- Name: poi_sources; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.poi_sources (
    id integer DEFAULT nextval('public.poi_sources_sequence'::regclass) NOT NULL,
    source character varying(3),
    remote_source_id character varying(255),
    author integer NOT NULL,
    locale_id integer NOT NULL
);


ALTER TABLE public.poi_sources OWNER TO kabano;

--
-- Name: poi_versions_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.poi_versions_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.poi_versions_sequence OWNER TO kabano;

--
-- Name: pois_sequence; Type: SEQUENCE; Schema: public; Owner: kabano
--

CREATE SEQUENCE public.pois_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pois_sequence OWNER TO kabano;

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


ALTER TABLE public.users_id_sequence OWNER TO kabano;

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
-- Name: poi_comments poi_comments_permalink_version_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_permalink_version_key UNIQUE (permalink, version);


--
-- Name: poi_comments poi_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_pkey PRIMARY KEY (id);


--
-- Name: poi_contributors poi_contributors_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_contributors
    ADD CONSTRAINT poi_contributors_pkey PRIMARY KEY (id);


--
-- Name: poi_contributors poi_contributors_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_contributors
    ADD CONSTRAINT poi_contributors_unique UNIQUE (poi, contributor);


--
-- Name: poi_locales poi_localised_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_locales
    ADD CONSTRAINT poi_localised_pkey PRIMARY KEY (id);


--
-- Name: poi_locales poi_localised_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_locales
    ADD CONSTRAINT poi_localised_unique UNIQUE (locale, poi_id);


--
-- Name: poi_sources poi_sources_locale_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_sources
    ADD CONSTRAINT poi_sources_locale_key UNIQUE (locale_id, source);


--
-- Name: poi_sources poi_sources_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_sources
    ADD CONSTRAINT poi_sources_pkey PRIMARY KEY (id);


--
-- Name: poi_sources poi_sources_source_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_sources
    ADD CONSTRAINT poi_sources_source_key UNIQUE (source, remote_source_id);


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
-- Name: poi_comments_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX poi_comments_is_archive_index ON public.poi_comments USING btree (is_archive);


--
-- Name: poi_comments_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX poi_comments_is_public_index ON public.poi_comments USING btree (is_public);


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
-- Name: content_versions content_versions_locale; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_versions
    ADD CONSTRAINT content_versions_locale FOREIGN KEY (locale_id) REFERENCES public.content_locales(id);


--
-- Name: poi_comments poi_comments_author_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_author_fkey FOREIGN KEY (author) REFERENCES public.users(id);


--
-- Name: poi_comments poi_comments_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- Name: poi_comments poi_comments_poi_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_poi_fkey FOREIGN KEY (poi) REFERENCES public.poi_sources(id);


--
-- Name: poi_contributors poi_contributors_contributor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_contributors
    ADD CONSTRAINT poi_contributors_contributor_fkey FOREIGN KEY (contributor) REFERENCES public.users(id);


--
-- Name: poi_contributors poi_contributors_poi_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_contributors
    ADD CONSTRAINT poi_contributors_poi_fkey FOREIGN KEY (poi) REFERENCES public.poi_sources(id);


--
-- Name: poi_locales poi_localised_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_locales
    ADD CONSTRAINT poi_localised_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- Name: poi_sources poi_sources_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_sources
    ADD CONSTRAINT poi_sources_locale_fkey FOREIGN KEY (source) REFERENCES public.sources(id) MATCH FULL;


--
-- Name: poi_sources poi_sources_poi_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_sources
    ADD CONSTRAINT poi_sources_poi_locale_fkey FOREIGN KEY (locale_id) REFERENCES public.poi_locales(id) MATCH FULL;


--
-- Name: users users_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO kabano;


--
-- PostgreSQL database dump complete
--

