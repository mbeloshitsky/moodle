<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" >
<xsl:output indent="yes" encoding="UTF-8"/>
<xsl:template match="/">
  <root>
  <manifest identifier="man00001">
    <organization default="toc00001">
      <tableofcontents identifier="toc00001"/>
    </organization>
    <resources>
      <xsl:for-each select="/files/questions/question_categories/question_category" >
        <xsl:if test="count(questions/question) > 0">
          <resource type="assessment/x-bb-pool">
            <xsl:attribute name="file">res<xsl:value-of select='format-number(position(), "00000")' />.dat</xsl:attribute>
            <xsl:attribute name="baseurl">res<xsl:value-of select='format-number(position(), "00000")' /></xsl:attribute>
            <xsl:attribute name="identifier">res<xsl:value-of select='format-number(position(), "00000")' /></xsl:attribute>
          </resource>
        </xsl:if>
      </xsl:for-each>
     </resources>
  </manifest>
  <filestocreate>

<xsl:for-each select="/files/questions/question_categories/question_category" >
  <xsl:if test="count(questions/question) > 0">
    <!-- ******** FILE ******** -->
    <file>
      <xsl:attribute name="name">res<xsl:value-of select='format-number(position(), "00000")' />.dat</xsl:attribute>

<POOL>
  <COURSEID value="IMPORT" />
  <TITLE>
    <xsl:attribute name="value"><xsl:value-of select="name"/></xsl:attribute>
  </TITLE>
  <DESCRIPTION>
    <TEXT><xsl:value-of select="info"/></TEXT>
  </DESCRIPTION>
  <DATES>
    <CREATED value="2014-02-05 18:21:26Z" />
    <UPDATED value="2014-02-05 18:21:26Z" />
  </DATES>
  <QUESTIONLIST>
      <xsl:for-each select="questions/question" >
        <xsl:if test="qtype = 'multichoice'">
            <xsl:choose>
              <xsl:when test="(qtype = 'multichoice') and (plugin_qtype_multichoice_question/multichoice/single = 1)">
                <QUESTION>
                  <xsl:attribute name="id">q<xsl:value-of select='position()' /></xsl:attribute>
                  <xsl:attribute name="class">QUESTION_MULTIPLECHOICE</xsl:attribute>
                </QUESTION>
              </xsl:when>
              <xsl:when test="(qtype = 'multichoice') and (plugin_qtype_multichoice_question/multichoice/single = 0)">
               <QUESTION>
                  <xsl:attribute name="id">q<xsl:value-of select='position()' /></xsl:attribute>
                  <xsl:attribute name="class">QUESTION_MULTIPLEANSWER</xsl:attribute>
                </QUESTION>
              </xsl:when>
              <xsl:when test="qtype = 'truefalse'">
                <QUESTION>
                  <xsl:attribute name="id">q<xsl:value-of select='position()' /></xsl:attribute>
                  <xsl:attribute name="class">QUESTION_TRUEFALSE</xsl:attribute>
                </QUESTION>
              </xsl:when>
              <xsl:when test="qtype = 'match'">
                <QUESTION>
                  <xsl:attribute name="id">q<xsl:value-of select='position()' /></xsl:attribute>
                  <xsl:attribute name="class">QUESTION_MATCH</xsl:attribute>
                </QUESTION>
              </xsl:when>
              <xsl:when test="qtype = 'shortanswer'">
                <QUESTION>
                  <xsl:attribute name="id">q<xsl:value-of select='position()' /></xsl:attribute>
                  <xsl:attribute name="class">QUESTION_FILLINBLANK</xsl:attribute>
                </QUESTION>
              </xsl:when>
            </xsl:choose>
        </xsl:if>
    </xsl:for-each>
  </QUESTIONLIST>
      <xsl:for-each select="questions/question" >
        <xsl:choose>
              <!-- ********************************************************************** -->
              <xsl:when test="(qtype = 'multichoice') and (plugin_qtype_multichoice_question/multichoice/single = 1)">
                <QUESTION_MULTIPLECHOICE>
                    <xsl:variable name="qid2" select="@id" />
                    <xsl:variable name="qid" select="position()" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' /></xsl:attribute>
                    <DATES>
                      <CREATED value="2014-02-05 18:21:26Z" />
                      <UPDATED value="2014-02-05 18:21:26Z" />
                    </DATES>
                    <BODY>
                      <TEXT><xsl:value-of select='questiontext' /></TEXT>
                      <xsl:for-each select="/files/files/files/file[component='question' and filearea='questiontext' and itemid=$qid2 and filename!='.']">
                        <file>
                          <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                          <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                          <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                          <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                        </file>
                      </xsl:for-each>
                      <FLAGS value="true">
                        <ISHTML value="true" />
                        <ISNEWLINELITERAL />
                      </FLAGS>
                    </BODY>
                    <xsl:for-each select="plugin_qtype_multichoice_question/answers/answer" >
                      <ANSWER position="{position()}">
                      <xsl:variable name="aid" select="@id" />
                      <xsl:attribute name="id">q<xsl:value-of select='$qid' />_a<xsl:value-of select='$aid' /></xsl:attribute>
                        <DATES>
                          <CREATED value="2014-02-05 18:21:26Z" />
                          <UPDATED value="2014-02-05 18:21:26Z" />
                        </DATES>
                        <TEXT><xsl:value-of select='answertext' /></TEXT>
                        <xsl:for-each select="/files/files/files/file[component='question' and filearea='answer' and itemid=$aid and filename!='.']">
                          <file>
                            <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                            <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                            <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                            <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                          </file>
                        </xsl:for-each>
                      </ANSWER>
                    </xsl:for-each>
                    <GRADABLE>
                      <FEEDBACK_WHEN_CORRECT></FEEDBACK_WHEN_CORRECT>
                      <FEEDBACK_WHEN_INCORRECT></FEEDBACK_WHEN_INCORRECT>
                      <xsl:for-each select="plugin_qtype_multichoice_question/answers/answer" >
                        <xsl:if test="number(fraction) > number(0.1)">
                          <CORRECTANSWER>
                            <xsl:attribute name="answer_id">q<xsl:value-of select='$qid' />_a<xsl:value-of select="@id" /></xsl:attribute>
                          </CORRECTANSWER>
                        </xsl:if>
                      </xsl:for-each>
                    </GRADABLE>
                  </QUESTION_MULTIPLECHOICE>
              </xsl:when>

              <!-- ********************************************************************** -->
              <xsl:when test="(qtype = 'multichoice') and (plugin_qtype_multichoice_question/multichoice/single = 0)">
                  <QUESTION_MULTIPLEANSWER>
                    <xsl:variable name="qid2" select="@id" />
                    <xsl:variable name="qid" select="position()" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' /></xsl:attribute>
                  <DATES>
                    <CREATED value="2014-02-05 18:21:26Z" />
                    <UPDATED value="2014-02-05 18:21:26Z" />
                  </DATES>
                  <BODY>
                    <TEXT><xsl:value-of select='questiontext' /></TEXT>
                    <xsl:for-each select="/files/files/files/file[component='question' and filearea='questiontext' and itemid=$qid2 and filename!='.']">
                        <file>
                          <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                          <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                          <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                          <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                        </file>
                    </xsl:for-each>
                    <FLAGS value="true">
                      <ISHTML value="true" />
                      <ISNEWLINELITERAL />
                    </FLAGS>
                  </BODY>
                  <xsl:for-each select="plugin_qtype_multichoice_question/answers/answer" >
                    <ANSWER position="{position()}">
                    <xsl:variable name="aid" select="@id" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' />_a<xsl:value-of select='$aid' /></xsl:attribute>
                      <DATES>
                        <CREATED value="2014-02-05 18:21:26Z" />
                        <UPDATED value="2014-02-05 18:21:26Z" />
                      </DATES>
                      <TEXT><xsl:value-of select='answertext' /></TEXT>
                      <xsl:for-each select="/files/files/files/file[component='question' and filearea='answer' and itemid=$aid and filename!='.']">
                        <file>
                          <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                          <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                          <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                          <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                        </file>
                      </xsl:for-each>
                    </ANSWER>
                  </xsl:for-each>
                  <GRADABLE>
                    <FEEDBACK_WHEN_CORRECT></FEEDBACK_WHEN_CORRECT>
                    <FEEDBACK_WHEN_INCORRECT></FEEDBACK_WHEN_INCORRECT>
                    <xsl:for-each select="plugin_qtype_multichoice_question/answers/answer" >
                      <xsl:if test="number(fraction) > number(0.1)">
                        <CORRECTANSWER>
                          <xsl:attribute name="answer_id">q<xsl:value-of select='$qid' />_a<xsl:value-of select="@id" /></xsl:attribute>
                        </CORRECTANSWER>
                      </xsl:if>
                    </xsl:for-each>
                  </GRADABLE>
                </QUESTION_MULTIPLEANSWER>
              </xsl:when>

              <!-- ********************************************************************** -->
              <xsl:when test="qtype = 'truefalse'">
                <QUESTION_TRUEFALSE>
                    <xsl:variable name="qid2" select="@id" />
                    <xsl:variable name="qid" select="position()" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' /></xsl:attribute>
                  <DATES>
                    <CREATED value="2014-02-05 18:21:26Z" />
                    <UPDATED value="2014-02-05 18:21:26Z" />
                  </DATES>
                  <BODY>
                    <TEXT><xsl:value-of select='questiontext' /></TEXT>
                    <xsl:for-each select="/files/files/files/file[component='question' and filearea='questiontext' and itemid=$qid2 and filename!='.']">
                        <file>
                          <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                          <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                          <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                          <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                        </file>
                    </xsl:for-each>
                    <FLAGS value="true">
                      <ISHTML value="true" />
                      <ISNEWLINELITERAL />
                    </FLAGS>
                  </BODY>
                  <ANSWER position="1">
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' />_a1</xsl:attribute>
                    <DATES>
                      <CREATED value="2014-02-05 18:21:26Z" />
                      <UPDATED value="2014-02-05 18:21:26Z" />
                    </DATES>
                    <TEXT>True</TEXT>
                  </ANSWER>
                  <ANSWER position="2">
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' />_a2</xsl:attribute>
                    <DATES>
                      <CREATED value="2014-02-05 18:21:26Z" />
                      <UPDATED value="2014-02-05 18:21:26Z" />
                    </DATES>
                    <TEXT>False</TEXT>
                  </ANSWER>
                  <GRADABLE>
                    <FEEDBACK_WHEN_CORRECT></FEEDBACK_WHEN_CORRECT>
                    <FEEDBACK_WHEN_INCORRECT></FEEDBACK_WHEN_INCORRECT>
                    <xsl:for-each select="plugin_qtype_truefalse_question/answers/answer" >
                      <xsl:choose>
                        <xsl:when test="(number(fraction) > number(0.1)) and (position()=1)">
                          <CORRECTANSWER>
                            <xsl:attribute name="answer_id">q<xsl:value-of select='$qid' />_a1</xsl:attribute>
                          </CORRECTANSWER>
                        </xsl:when>
                        <xsl:when test="(number(fraction) > number(0.1)) and (position()=2)">
                          <CORRECTANSWER>
                            <xsl:attribute name="answer_id">q<xsl:value-of select='$qid' />_a2</xsl:attribute>
                          </CORRECTANSWER>
                        </xsl:when>
                      </xsl:choose>
                    </xsl:for-each>
                  </GRADABLE>
                </QUESTION_TRUEFALSE>
              </xsl:when>

              <!-- ********************************************************************** -->
              <xsl:when test="qtype = 'match'">
                 <QUESTION_MATCH>
                    <xsl:variable name="qid2" select="@id" />
                    <xsl:variable name="qid" select="position()" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' /></xsl:attribute>
                  <DATES>
                    <CREATED value="2014-02-05 18:21:26Z" />
                    <UPDATED value="2014-02-05 18:21:26Z" />
                  </DATES>
                  <BODY>
                    <TEXT><xsl:value-of select='questiontext' /></TEXT>
                    <xsl:for-each select="/files/files/files/file[component='question' and filearea='questiontext' and itemid=$qid2 and filename!='.']">
                     <file>
                        <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                        <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                        <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                        <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                      </file>
                    </xsl:for-each>
                    <FLAGS value="true">
                      <ISHTML value="true" />
                      <ISNEWLINELITERAL />
                    </FLAGS>
                  </BODY>
                  <xsl:for-each select="plugin_qtype_match_question/matches/match" >
                    <ANSWER placement="left" position="{position()}">
                    <xsl:variable name="aid" select="@id" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' />_a<xsl:value-of select='$aid' /></xsl:attribute>
                      <DATES>
                        <CREATED value="2014-02-05 18:21:26Z" />
                        <UPDATED value="2014-02-05 18:21:26Z" />
                      </DATES>
                      <TEXT><xsl:value-of select='questiontext' /></TEXT>
                      <xsl:for-each select="/files/files/files/file[component='question' and filearea='answer' and itemid=$aid and filename!='.']">
                        <file>
                          <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                          <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                          <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                          <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                        </file>
                      </xsl:for-each>
                    </ANSWER>
                  </xsl:for-each>
                  <xsl:for-each select="plugin_qtype_match_question/matches/match" >
                    <CHOICE placement="right" position="{position()}">
                    <xsl:variable name="aid" select="@id" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' />_c<xsl:value-of select='$aid' /></xsl:attribute>
                      <DATES>
                        <CREATED value="2014-02-05 18:21:26Z" />
                        <UPDATED value="2014-02-05 18:21:26Z" />
                      </DATES>
                      <TEXT><xsl:value-of select='answertext' /></TEXT>
                      <xsl:for-each select="/files/files/files/file[component='question' and filearea='answer' and itemid=$aid and filename!='.']">
                        <file>
                          <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                          <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                          <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                          <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                        </file>
                      </xsl:for-each>
                    </CHOICE>
                  </xsl:for-each>
                  <GRADABLE>
                    <FEEDBACK_WHEN_CORRECT></FEEDBACK_WHEN_CORRECT>
                    <FEEDBACK_WHEN_INCORRECT></FEEDBACK_WHEN_INCORRECT>
                    <xsl:for-each select="plugin_qtype_match_question/matches/match" >
                        <CORRECTANSWER>
                          <xsl:attribute name="answer_id">q<xsl:value-of select='$qid' />_a<xsl:value-of select="@id" /></xsl:attribute>
                          <xsl:attribute name="choice_id">q<xsl:value-of select='$qid' />_c<xsl:value-of select="@id" /></xsl:attribute>
                        </CORRECTANSWER>
                    </xsl:for-each>
                  </GRADABLE>
                </QUESTION_MATCH>
              </xsl:when>


               <!-- ********************************************************************** -->
              <xsl:when test="qtype = 'shortanswer'">
                 <QUESTION_FILLINBLANK>
                    <xsl:variable name="qid2" select="@id" />
                    <xsl:variable name="qid" select="position()" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' /></xsl:attribute>
                  <DATES>
                    <CREATED value="2014-02-05 18:21:26Z" />
                    <UPDATED value="2014-02-05 18:21:26Z" />
                  </DATES>
                  <BODY>
                    <TEXT><xsl:value-of select='questiontext' /></TEXT>
                    <xsl:for-each select="/files/files/files/file[component='question' and filearea='questiontext' and itemid=$qid2 and filename!='.']">
                      <file>
                        <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                        <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                        <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                        <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                      </file>
                    </xsl:for-each>
                    <FLAGS value="true">
                      <ISHTML value="true" />
                      <ISNEWLINELITERAL />
                    </FLAGS>
                  </BODY>
                  <xsl:for-each select="plugin_qtype_shortanswer_question/answers/answer" >
                    <ANSWER position="{position()}">
                    <xsl:variable name="aid" select="@id" />
                    <xsl:attribute name="id">q<xsl:value-of select='$qid' />_a<xsl:value-of select='@id' /></xsl:attribute>
                      <DATES>
                        <CREATED value="2014-02-05 18:21:26Z" />
                        <UPDATED value="2014-02-05 18:21:26Z" />
                      </DATES>
                      <TEXT><xsl:value-of select='answertext' /></TEXT>
                      <xsl:for-each select="/files/files/files/file[component='question' and filearea='answer' and itemid=$aid and filename!='.']">
                        <file>
                          <xsl:attribute name="hash"><xsl:value-of select="contenthash" /></xsl:attribute>
                          <xsl:attribute name="name"><xsl:value-of select="filename" /></xsl:attribute>
                          <xsl:attribute name="mime"><xsl:value-of select="mimetype" /></xsl:attribute>
                          <xsl:attribute name="alt"><xsl:value-of select="source" /></xsl:attribute>
                        </file>
                      </xsl:for-each>
                    </ANSWER>
                  </xsl:for-each>
                  <GRADABLE>
                    <FEEDBACK_WHEN_CORRECT></FEEDBACK_WHEN_CORRECT>
                    <FEEDBACK_WHEN_INCORRECT></FEEDBACK_WHEN_INCORRECT>
                  </GRADABLE>
                </QUESTION_FILLINBLANK>
              </xsl:when>


            </xsl:choose>
  </xsl:for-each>
</POOL>
      
    </file>
 </xsl:if>
  </xsl:for-each>

  </filestocreate>
  <filestocopy>
  </filestocopy>
  </root>
  </xsl:template>
</xsl:stylesheet>
