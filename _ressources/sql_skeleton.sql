--
-- PostgreSQL database dump
--

-- Dumped from database version 10.5
-- Dumped by pg_dump version 10.5

-- Started on 2018-08-20 19:41:09 CEST

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 7 (class 2615 OID 17905)
-- Name: topology; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA topology;


ALTER SCHEMA topology OWNER TO postgres;

--
-- TOC entry 3822 (class 0 OID 0)
-- Dependencies: 7
-- Name: SCHEMA topology; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA topology IS 'PostGIS Topology schema';


--
-- TOC entry 1 (class 3079 OID 12281)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 3823 (class 0 OID 0)
-- Dependencies: 1
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- TOC entry 2 (class 3079 OID 16398)
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- TOC entry 3824 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';


--
-- TOC entry 3 (class 3079 OID 17906)
-- Name: postgis_topology; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS postgis_topology WITH SCHEMA topology;


--
-- TOC entry 3825 (class 0 OID 0)
-- Dependencies: 3
-- Name: EXTENSION postgis_topology; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis_topology IS 'PostGIS topology spatial types and functions';


--
-- TOC entry 2010 (class 1247 OID 18254)
-- Name: content_type_enum; Type: TYPE; Schema: public; Owner: kabano
--

CREATE TYPE public.content_type_enum AS ENUM (
    'wiki',
    'blog',
    'forum'
);


ALTER TYPE public.content_type_enum OWNER TO kabano;

--
-- TOC entry 1995 (class 1247 OID 18166)
-- Name: poi_key_enum; Type: TYPE; Schema: public; Owner: kabano
--

CREATE TYPE public.poi_key_enum AS ENUM (
    'description',
    'access'
);


ALTER TYPE public.poi_key_enum OWNER TO kabano;

--
-- TOC entry 1981 (class 1247 OID 18088)
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
-- TOC entry 1975 (class 1247 OID 18057)
-- Name: user_rank_enum; Type: TYPE; Schema: public; Owner: kabano
--

CREATE TYPE public.user_rank_enum AS ENUM (
    'blocked',
    'registered',
    'premium',
    'moderator',
    'administrator'
);


ALTER TYPE public.user_rank_enum OWNER TO kabano;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 229 (class 1259 OID 18288)
-- Name: content_comments; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.content_comments (
    id integer NOT NULL,
    permalink character varying(255),
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
-- TOC entry 228 (class 1259 OID 18271)
-- Name: content_contributors; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.content_contributors (
    id integer NOT NULL,
    content integer,
    contributor integer
);


ALTER TABLE public.content_contributors OWNER TO kabano;

--
-- TOC entry 227 (class 1259 OID 18230)
-- Name: contents; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.contents (
    id integer NOT NULL,
    permalink character varying(255) NOT NULL,
    version integer DEFAULT 0 NOT NULL,
    locale character varying(32) NOT NULL,
    creation_date timestamp without time zone NOT NULL,
    update_date timestamp without time zone NOT NULL,
    author integer NOT NULL,
    is_public boolean DEFAULT true NOT NULL,
    is_archive boolean DEFAULT false NOT NULL,
    is_commentable boolean DEFAULT true NOT NULL,
    type public.content_type_enum NOT NULL,
    name character varying(255),
    content text
);


ALTER TABLE public.contents OWNER TO kabano;

--
-- TOC entry 221 (class 1259 OID 18067)
-- Name: locales; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.locales (
    name character varying(32) NOT NULL,
    display_name character varying(255) NOT NULL,
    flag_name character varying(32)
);


ALTER TABLE public.locales OWNER TO kabano;

--
-- TOC entry 226 (class 1259 OID 18203)
-- Name: poi_comments; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.poi_comments (
    id integer NOT NULL,
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
-- TOC entry 225 (class 1259 OID 18186)
-- Name: poi_contributors; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.poi_contributors (
    id integer NOT NULL,
    poi integer,
    contributor integer
);


ALTER TABLE public.poi_contributors OWNER TO kabano;

--
-- TOC entry 224 (class 1259 OID 18160)
-- Name: poi_localised; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.poi_localised (
    id integer NOT NULL,
    poi integer NOT NULL,
    locale character varying(32) NOT NULL,
    key public.poi_key_enum NOT NULL,
    value text NOT NULL
);


ALTER TABLE public.poi_localised OWNER TO kabano;

--
-- TOC entry 222 (class 1259 OID 18109)
-- Name: poi_sources; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.poi_sources (
    id character varying(3) NOT NULL,
    display_name character varying(255) NOT NULL,
    icon_name character varying(255),
    website character varying(255),
    license_name character varying(255),
    license_url character varying(255)
);


ALTER TABLE public.poi_sources OWNER TO kabano;

--
-- TOC entry 223 (class 1259 OID 18119)
-- Name: pois; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.pois (
    id integer NOT NULL,
    permalink character varying(255) NOT NULL,
    version integer DEFAULT 0 NOT NULL,
    creation_date timestamp without time zone NOT NULL,
    update_date timestamp without time zone NOT NULL,
    author integer NOT NULL,
    is_public boolean DEFAULT true NOT NULL,
    is_archive boolean DEFAULT false NOT NULL,
    type public.poi_type_enum NOT NULL,
    is_detroyed boolean DEFAULT false NOT NULL,
    name character varying(255) NOT NULL,
    alt_names character varying(255),
    source character varying(3),
    source_id character varying(255),
    "position" public.geometry NOT NULL,
    parameters jsonb
);


ALTER TABLE public.pois OWNER TO kabano;

--
-- TOC entry 220 (class 1259 OID 18047)
-- Name: users; Type: TABLE; Schema: public; Owner: kabano
--

CREATE TABLE public.users (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    version integer DEFAULT 0 NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    website character varying(255),
    is_avatar_present boolean NOT NULL,
    is_archive boolean NOT NULL,
    rank public.user_rank_enum NOT NULL,
    locale character varying(32) NOT NULL,
    timezone character varying(8) NOT NULL,
    visit_date timestamp without time zone NOT NULL,
    register_date timestamp without time zone NOT NULL
);


ALTER TABLE public.users OWNER TO kabano;

--
-- TOC entry 3667 (class 2606 OID 18297)
-- Name: content_comments content_comments_permalink_version_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_permalink_version_key UNIQUE (permalink, version);


--
-- TOC entry 3669 (class 2606 OID 18295)
-- Name: content_comments content_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_pkey PRIMARY KEY (id);


--
-- TOC entry 3661 (class 2606 OID 18275)
-- Name: content_contributors content_contributors_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_pkey PRIMARY KEY (id);


--
-- TOC entry 3663 (class 2606 OID 18277)
-- Name: content_contributors content_contributors_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_unique UNIQUE (content, contributor);


--
-- TOC entry 3657 (class 2606 OID 18262)
-- Name: contents contents_permalink_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_permalink_unique UNIQUE (permalink, version, locale);


--
-- TOC entry 3659 (class 2606 OID 18238)
-- Name: contents contents_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_pkey PRIMARY KEY (id);


--
-- TOC entry 3623 (class 2606 OID 18073)
-- Name: locales locales_display_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_display_name_unique UNIQUE (display_name);


--
-- TOC entry 3625 (class 2606 OID 18075)
-- Name: locales locales_flag_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_flag_name_unique UNIQUE (flag_name);


--
-- TOC entry 3627 (class 2606 OID 18071)
-- Name: locales locales_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.locales
    ADD CONSTRAINT locales_pkey PRIMARY KEY (name);


--
-- TOC entry 3651 (class 2606 OID 18212)
-- Name: poi_comments poi_comments_permalink_version_key; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_permalink_version_key UNIQUE (permalink, version);


--
-- TOC entry 3653 (class 2606 OID 18210)
-- Name: poi_comments poi_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_pkey PRIMARY KEY (id);


--
-- TOC entry 3645 (class 2606 OID 18190)
-- Name: poi_contributors poi_contributors_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_contributors
    ADD CONSTRAINT poi_contributors_pkey PRIMARY KEY (id);


--
-- TOC entry 3647 (class 2606 OID 18192)
-- Name: poi_contributors poi_contributors_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_contributors
    ADD CONSTRAINT poi_contributors_unique UNIQUE (poi, contributor);


--
-- TOC entry 3641 (class 2606 OID 18164)
-- Name: poi_localised poi_localised_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_localised
    ADD CONSTRAINT poi_localised_pkey PRIMARY KEY (id);


--
-- TOC entry 3643 (class 2606 OID 18185)
-- Name: poi_localised poi_localised_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_localised
    ADD CONSTRAINT poi_localised_unique UNIQUE (poi, locale, key);


--
-- TOC entry 3629 (class 2606 OID 18118)
-- Name: poi_sources poi_sources_display_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_sources
    ADD CONSTRAINT poi_sources_display_name_unique UNIQUE (display_name);


--
-- TOC entry 3631 (class 2606 OID 18116)
-- Name: poi_sources poi_sources_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_sources
    ADD CONSTRAINT poi_sources_pkey PRIMARY KEY (id);


--
-- TOC entry 3636 (class 2606 OID 18145)
-- Name: pois pois_permalink_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.pois
    ADD CONSTRAINT pois_permalink_unique UNIQUE (permalink, version);


--
-- TOC entry 3638 (class 2606 OID 18124)
-- Name: pois pois_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.pois
    ADD CONSTRAINT pois_pkey PRIMARY KEY (id);


--
-- TOC entry 3615 (class 2606 OID 18079)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 3618 (class 2606 OID 18077)
-- Name: users users_name_unique; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_name_unique UNIQUE (name, version);


--
-- TOC entry 3620 (class 2606 OID 18055)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 3664 (class 1259 OID 18313)
-- Name: content_comments_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX content_comments_is_archive_index ON public.content_comments USING btree (is_archive);


--
-- TOC entry 3665 (class 1259 OID 18314)
-- Name: content_comments_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX content_comments_is_public_index ON public.content_comments USING btree (is_public);


--
-- TOC entry 3654 (class 1259 OID 18251)
-- Name: contents_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX contents_is_archive_index ON public.contents USING btree (is_archive);


--
-- TOC entry 3655 (class 1259 OID 18252)
-- Name: contents_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX contents_is_public_index ON public.contents USING btree (is_public);


--
-- TOC entry 3648 (class 1259 OID 18229)
-- Name: poi_comments_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX poi_comments_is_archive_index ON public.poi_comments USING btree (is_archive);


--
-- TOC entry 3649 (class 1259 OID 18228)
-- Name: poi_comments_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX poi_comments_is_public_index ON public.poi_comments USING btree (is_public);


--
-- TOC entry 3632 (class 1259 OID 18157)
-- Name: pois_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX pois_is_archive_index ON public.pois USING btree (is_archive);


--
-- TOC entry 3633 (class 1259 OID 18159)
-- Name: pois_is_destroyed_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX pois_is_destroyed_index ON public.pois USING btree (is_detroyed);


--
-- TOC entry 3634 (class 1259 OID 18156)
-- Name: pois_is_public_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX pois_is_public_index ON public.pois USING btree (is_public);


--
-- TOC entry 3639 (class 1259 OID 18158)
-- Name: pois_type_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX pois_type_index ON public.pois USING btree (type);


--
-- TOC entry 3616 (class 1259 OID 18080)
-- Name: users_is_archive_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX users_is_archive_index ON public.users USING btree (is_archive);


--
-- TOC entry 3621 (class 1259 OID 18081)
-- Name: users_register_date_index; Type: INDEX; Schema: public; Owner: kabano
--

CREATE INDEX users_register_date_index ON public.users USING btree (register_date);


--
-- TOC entry 3684 (class 2606 OID 18298)
-- Name: content_comments content_comments_author_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_author_fkey FOREIGN KEY (author) REFERENCES public.users(id);


--
-- TOC entry 3686 (class 2606 OID 18308)
-- Name: content_comments content_comments_content_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_content_fkey FOREIGN KEY (content) REFERENCES public.contents(id);


--
-- TOC entry 3685 (class 2606 OID 18303)
-- Name: content_comments content_comments_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_comments
    ADD CONSTRAINT content_comments_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- TOC entry 3683 (class 2606 OID 18283)
-- Name: content_contributors content_contributors_content_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_content_fkey FOREIGN KEY (content) REFERENCES public.contents(id);


--
-- TOC entry 3682 (class 2606 OID 18278)
-- Name: content_contributors content_contributors_contributor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.content_contributors
    ADD CONSTRAINT content_contributors_contributor_fkey FOREIGN KEY (contributor) REFERENCES public.users(id);


--
-- TOC entry 3680 (class 2606 OID 18241)
-- Name: contents contents_author_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_author_fkey FOREIGN KEY (author) REFERENCES public.users(id);


--
-- TOC entry 3681 (class 2606 OID 18263)
-- Name: contents contents_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- TOC entry 3677 (class 2606 OID 18213)
-- Name: poi_comments poi_comments_author_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_author_fkey FOREIGN KEY (author) REFERENCES public.users(id);


--
-- TOC entry 3678 (class 2606 OID 18218)
-- Name: poi_comments poi_comments_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- TOC entry 3679 (class 2606 OID 18223)
-- Name: poi_comments poi_comments_poi_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_comments
    ADD CONSTRAINT poi_comments_poi_fkey FOREIGN KEY (poi) REFERENCES public.pois(id);


--
-- TOC entry 3675 (class 2606 OID 18193)
-- Name: poi_contributors poi_contributors_contributor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_contributors
    ADD CONSTRAINT poi_contributors_contributor_fkey FOREIGN KEY (contributor) REFERENCES public.users(id);


--
-- TOC entry 3676 (class 2606 OID 18198)
-- Name: poi_contributors poi_contributors_poi_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_contributors
    ADD CONSTRAINT poi_contributors_poi_fkey FOREIGN KEY (poi) REFERENCES public.pois(id);


--
-- TOC entry 3674 (class 2606 OID 18179)
-- Name: poi_localised poi_localised_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_localised
    ADD CONSTRAINT poi_localised_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


--
-- TOC entry 3673 (class 2606 OID 18174)
-- Name: poi_localised poi_localised_poi_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.poi_localised
    ADD CONSTRAINT poi_localised_poi_fkey FOREIGN KEY (poi) REFERENCES public.pois(id);


--
-- TOC entry 3671 (class 2606 OID 18146)
-- Name: pois pois_author_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.pois
    ADD CONSTRAINT pois_author_fkey FOREIGN KEY (author) REFERENCES public.users(id);


--
-- TOC entry 3672 (class 2606 OID 18151)
-- Name: pois pois_source_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.pois
    ADD CONSTRAINT pois_source_fkey FOREIGN KEY (source) REFERENCES public.poi_sources(id);


--
-- TOC entry 3670 (class 2606 OID 18082)
-- Name: users users_locale_fkey; Type: FK CONSTRAINT; Schema: public; Owner: kabano
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_locale_fkey FOREIGN KEY (locale) REFERENCES public.locales(name);


-- Completed on 2018-08-20 19:41:10 CEST

--
-- PostgreSQL database dump complete
--

