--
-- Table structure for table 'oai_records'
--
-- Copyright (c) 2003 Heinrich Stamerjohanns
--                   stamer@uni-oldenburg.de
--
-- $Id: oai_records.sql,v 1.1.1.1 2003/04/08 12:05:18 stamer Exp $
--
--
--CREATE TABLE oai_records (
--	serial serial,
--	provider varchar(255),
--	url varchar(255),
--	enterdate timestamp,
--	oai_identifier varchar(255),
--	oai_set varchar(255),
--	datestamp timestamp,
--	deleted boolean default false,
--	dc_title varchar(255),
--	dc_creator text,
--	dc_subject varchar(255),
--	dc_description text,
--	dc_contributor varchar(255),
--	dc_publisher varchar(255),
--	dc_date date,
--	dc_type varchar(255),
--	dc_format varchar(255),
--	dc_identifier varchar(255),
--	dc_source varchar(255),
--	dc_language varchar(255),
--	dc_relation varchar(255),
--	dc_coverage varchar(255),
--	dc_rights varchar(255),
--	PRIMARY KEY (serial)
--);

CREATE VIEW oai_records AS
   SELECT d.id as serial, d.id as url, d.id as oai_idenfifier , 'biblio' as oai_set , d.titulo as dc_title , d.autores_nome_completo_normalizado as dc_creator , d.descricao as dc_description,
   	do.data_inclusao as dc_date , d.nome_tipodocumento as dc_type , m.mime as dc_format, d.id as identifier , m.fonte as dc_source , d.nome_idioma as dc_language , do.abrangencia as dc_coverage , do.direitos as dc_rights
   FROM DocumentoConsulta d, Documento do , Midia m
   WHERE d.id = do.id AND m.Documento_id = d.id



CREATE VIEW oai_records AS
   SELECT m.id as serial, CONCAT('http://www.literaturabrasileira.ufsc.br/documentos/?action=download&id=',m.id) as url, m.id as oai_idenfifier , 'biblio' as oai_set , CONCAT_WS(' ',d.titulo_normalizado, CONCAT('- ',pt_normalize(m.titulo))) as dc_title , d.autores_nome_completo_normalizado as dc_creator , pt_normalize(d.descricao) as dc_description,
   	do.data_inclusao as dc_date, do.data_inclusao as datestamp , d.nome_tipodocumento as dc_type , m.mime as dc_format, m.id as identifier , m.fonte as dc_source , i.iso as dc_language , do.abrangencia as dc_coverage , do.direitos as dc_rights
   FROM Midia m ,DocumentoConsulta d, Documento do , Idioma i
   WHERE m.Documento_id = d.id AND d.id = do.id AND do.Idioma_id = i.id