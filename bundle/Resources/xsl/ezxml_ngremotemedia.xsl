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
        <div>
            <xsl:attribute name="class">remote-image-inline <xsl:value-of select="@custom:cssclass"/></xsl:attribute>

            <xsl:choose>
                <xsl:when test="@custom:resourceType='image'">
                    <img>
                        <xsl:attribute name="src"><xsl:value-of select="@custom:src"/></xsl:attribute>
                        <xsl:attribute name="alt"><xsl:value-of select="@custom:alt"/></xsl:attribute>
                    </img><div class="img-caption"><xsl:value-of select="@custom:caption" /></div>
                </xsl:when>

                <xsl:when test="@custom:resourceType='video'">
                    <xsl:value-of select="@custom:videoTag"/>
                </xsl:when>

                <xsl:otherwise>
                    <a>
                        <xsl:attribute name="href"><xsl:value-of select="@custom:src"/></xsl:attribute>
                        <xsl:value-of select="@custom:src"/>
                    </a>
                </xsl:otherwise>
            </xsl:choose>
        </div>
    </xsl:template>
</xsl:stylesheet>
