<?xml version="1.0" encoding="utf8"?>
<!--
  Turn atom feed xml into rdf.
  Inspired by http://www.w3.org/TR/grddl-tests/#xmlWithGrddlAttributeAndNonXMLNamespaceDocument

  $ shaarli_url=http://<shaarli install base url>

  $ # fast:
  $ xsltproc $shaarli_url/inc/atom2rdf.xslt "$shaarli_url/?do=atom&nb=all"
  $ xsltproc $shaarli_url/inc/atom2rdf.xslt "$shaarli_url/?do=atom&nb=all" | rapper -i rdfxml -o turtle - $shaarli_url/

  $ # slow due to timeout on atom namespace document:
  $ rapper -t -i grddl -o turtle "$shaarli_url/?do=atom&nb=all" "$shaarli_url/"

  http://xmlsoft.org/XSLT/xsltproc2.html
  http://librdf.org/raptor/rapper.html
  http://www.w3.org/TR/xslt
-->
<xsl:stylesheet
    xmlns:a="http://www.w3.org/2005/Atom"
    xmlns:dct="http://purl.org/dc/terms/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema#"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    exclude-result-prefixes="xsl a dc"
    version="1.0">
  <xsl:output
    method="xml"
    encoding="utf8"
    omit-xml-declaration="no"
    indent="yes"
  />

  <xsl:template match="a:feed">
    <rdf:RDF>
      <rdf:Description rdf:about="">
        <dct:title><xsl:value-of select="a:title"/></dct:title>
        <dct:source rdf:resource="{a:link/@href}"/>
        <dct:modified><xsl:value-of select="a:updated"/></dct:modified>
        <dct:identifier rdf:resource="{a:id}"/>
        <!-- dct:publisher><xsl:value-of select="a:author/a:name"/></dct:publisher -->
        <dct:publisher rdf:resource="{a:author/a:uri}"/>
        <dct:creator rdf:resource="http://sebsauvage.net/wiki/doku.php?id=php:shaarli"/>
      </rdf:Description>
      <xsl:apply-templates select="a:entry"/>
    </rdf:RDF>
  </xsl:template>

  <xsl:template match="a:entry">
    <rdf:Description rdf:about="{a:link/@href}">
      <dct:identifier rdf:resource="{a:id}"/>
      <dct:title><xsl:value-of select="a:title"/></dct:title>
      <dct:modified><xsl:value-of select="a:updated"/></dct:modified>
      <dct:description><xsl:value-of select="a:content"/></dct:description>
      <xsl:for-each select="a:category">
        <dct:subject rdf:resource="{@scheme}{@term}"/>
      </xsl:for-each>
    </rdf:Description>
  </xsl:template>
</xsl:stylesheet>
