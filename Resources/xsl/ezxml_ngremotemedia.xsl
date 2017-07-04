<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
        xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"
        xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
        exclude-result-prefixes="xhtml custom image">

    <xsl:output method="html" indent="yes" encoding="UTF-8"/>

    <xsl:template match="custom[@name='ngremotemedia']">
        <img>
            <xsl:attribute name="src"><xsl:value-of select="@custom:image_url"/></xsl:attribute>
            <xsl:attribute name="class"><xsl:value-of select="@custom:cssclass"/></xsl:attribute>
            <xsl:attribute name="alt"><xsl:value-of select="@custom:alttext"/></xsl:attribute>
        </img><div class="img-caption"><xsl:value-of select="@custom:caption" /></div>
    </xsl:template>
</xsl:stylesheet>
