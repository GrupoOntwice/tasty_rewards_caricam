<?php
/**
 * @file
 * Contains \Drupal\fancy_login\Controller\FancyLoginController.
 */

namespace Drupal\pepsicontest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Drupal\pepsibam\ReportGenerator;

class ReportingController extends ControllerBase {

    const TMP_CONTEST_TABLE = 'pepsicontest_reg_contest_tmp';
    public $startdate;
    public $enddate;
    public $language;
    public $lang_arr;
    public $questionid;
    /**
     * {@inheritdoc}
     */
    public function index() {

        \Drupal::service('page_cache_kill_switch')->trigger(); // Mark this page as being uncacheable.

        //Getting language and passing to twig
        $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();

        $data['title'] = t('Reporting');

        $data['contests'] = $this->getContest();

        return array(
            '#theme' => 'pepsicontest_reporting_template',
            '#data' => $data,
        );
    }


    function getContest() {

        $em = \Drupal::service('entity_type.manager');
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

        $query = $em->getStorage('node')->getQuery()
            ->condition('type', 'contest')
            ->sort('field_contest_uri', 'ASC')
        ;

        $nids = $query->execute();

        $contests = array();

        foreach ($nids as $nid) {
            $node = \Drupal\node\Entity\Node::load($nid);

            $questions = $node->getTranslation($langcode)->field_questions->referencedEntities();


            $questionid = [];
            foreach ($questions as $question) {
                $questionid[] = $question->nid->value;
            }
            /*echo "<pre>";
            \Doctrine\Common\Util\Debug::dump($questionid);
            echo "</pre>";
            */
            $contests[] = array('nid'=> $nid , 'contestname' => $node->getTranslation($langcode)->field_contest_uri->value, 'questionid'=>$questionid);
        }

        /*echo "<pre>";
            \Doctrine\Common\Util\Debug::dump($contests);
            echo "</pre>";
            exit;
        */
        return $contests;
    }

    public function ajax_chart(Request $request) {

        $params_arr = array('contestid', 'startdate', 'enddate', 'report', 'questionid', 'language');
        if (!empty(\Drupal::request()->request->all())) {
            foreach(\Drupal::request()->request->all() as $key => $param) {
                if ('format' == $key) {
                    $$key = trim($param) !== "" ? trim($param) : 'graph';
                } else {
                    $$key = trim($param);
                }
            }
        } else {
            foreach(\Drupal::request()->query->all() as $key => $param) {
                if ('format' == $key) {
                    $$key = trim($param) !== "" ? trim($param) : 'graph';
                } else {
                    $$key = trim($param);
                }
            }
        }

        $this->startdate = $startdate . ' 00:00:00';
        $this->enddate = $enddate . ' 23:59:29';
        $this->contestid = $contestid;
        //$this->questionid = isset($questionid)?($questionid>"")?$questionid:0;

        $questionid = isset($questionid)?$questionid:'0';

        if ( "" == $questionid) {
            $this->questionid = '0';
        }
        else {
            $this->questionid = $questionid;
        }
        $this->language = '';
        if (isset($language)) {
            $this->language = $language;
        }

        /*if (isset($format) && "" == $format) {
            $format = 'graph';
        }*/

        $format = isset($format)?$format:'graph';
        if ( "" == $format) {
            $format = 'graph';
        }

        $limit = 10;
        $this->lang_arr = array('en', 'fr');
        
        
        switch ($report) {
            case 'gender':
            case 'province':
                $return = $this->getReportData ($report,  $format);
                break;
            case 'date':
                $return = $this->getReportDataByDate($format);
                break;
            case 'entries_count_by_location':
                $return = $this->getReportEntriesCountByLocation ($format);
                break;
            case 'age_and_gender':
                $return = $this->getReportEntriesCountByAgeAndGender($format);
                break;
            case 'participant_count_by_age_and_gender':
                $return = $this->getReportParticipantCountByAgeAndGender($format);
                break;
            case 'entry_count_by_day':
                // See https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_get-format for more details about GET_FORMAT
                $return = $this->getReportDataGroupedByPeriod("GET_FORMAT(DATE,'ISO')", $format);
                break;
            case 'question':
                $return = $this->getReportAnswer($format);
                break;
            case 'getReportPollAnswersGroupedByDay':
                $return = $this->getReportPollAnswersGroupedByDay();
                break;
            case 'getReportPollChoicesParticipationCount':
                $return = $this->getReportPollChoicesParticipationCount();
                break;
            case 'getReportContestsUniqueParticipationByDate':
                $return = $this->getReportContestsUniqueParticipationByDate();
                break;
            case 'getReportContestParticipationData':
                $return = $this->getReportContestParticipationData();
                break;
            case 'pickwinner':
                $return = $this->pickwinner($limit, $format);
                break;
            default:
                $return = [];
                break;
        }

        return new JsonResponse($return);

    }

    function getReportData ($field, $format = "graph")
    {
        $rows = [];
        if ('graph' !== $format) {
            $rows[] = [$field ,'ncount', 'language'];
        }
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $select = \Drupal::database()->select('pepsicontest_reg_contest', 'c');
            $select->addField('c', $field);

            $select->addExpression('COUNT(c.' .$field . ')', 'ncount');
            $select->condition('c.contest_id', $this->contestid);
            $select->condition('c.regdate', $this->startdate, '>=');
            $select->condition('c.regdate', $this->enddate, '<=');
            $select->condition('c.language', $language);
            $select->groupBy("c.". $field );

            if($field == 'province'){
                $select->orderBy("ncount", "DESC");
            }else{
                $select->orderBy("c.". $field );
                $select->orderBy("c.". $field );
            }

            $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

            if ($format === 'graph') {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie[$field] ,intval($entrie['ncount']), $language];
                }
                return $rows;
            } else {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie[$field] ,intval($entrie['ncount']), $language];
                }
            }
        }
        $filename = 'Report_data_' . $this->getContestIdPart() . '_ON_FIELD_'. $field . $this->getFromPart();
        return $this->arrayToCsvDownload($rows, $filename . ".csv");
    }

    function getReportEntriesCountByLocation ($format = "graph")
    {
        $rows = [];
        if ('graph' !== $format) {
            $rows[] = ['city', 'ncount', 'language'];
        }
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $select = \Drupal::database()->select('pepsicontest_reg_contest', 'c');
            $select->addExpression('COUNT(*)', 'ncount');
            $select->addField('c', 'city');
            $select->condition('c.contest_id', $this->contestid);
            $select->condition('c.regdate', $this->startdate, '>=');
            $select->condition('c.regdate', $this->enddate, '<=');
            $select->condition('c.language', $language);
            $select->groupBy("c.city");
            $select->orderBy("ncount", "DESC");

            $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

            if ($format === 'graph') {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie['city'], intval($entrie['ncount']), $language];
                }
                return $rows;
            } else {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie['city'], intval($entrie['ncount']), $language];
                }
            }
        }
        $filename = 'ReportEntriesCountByLocation' . $this->getContestIdPart() . $this->getFromPart();
        return $this->arrayToCsvDownload($rows, $filename . ".csv");
    }

    function getReportParticipantCountGroupByField ($field, $format = 'graph')
    {
        $rows = [];
        if ('graph' !== $format) {
            $rows[] = [$field, 'ncount', 'language'];
        }
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $select = \Drupal::database()->select('pepsicontest_reg_contest', 'c');
            $select->distinct();
            $select->addExpression('COUNT(c.user_id)', 'ncount');
            $select->condition('c.contest_id', $this->contestid);
            $select->condition('c.regdate', $this->startdate, '>=');
            $select->condition('c.regdate', $this->enddate, '<=');
            $select->condition('c.language', $language);
            if (is_string($field)) {
                $select->groupBy("c." . $field);
            } else {
                foreach ($field as $current_field) {
                    $select->groupBy("c." . $current_field);
                }
            }

            if ($field == 'province') {
                $select->orderBy("ncount", "DESC");
            } else {
                $select->orderBy("c." . $field);
                $select->orderBy("c." . $field);
            }

            $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

            if ($format === 'graph') {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie[$field], intval($entrie['ncount']), $language];
                }
                return $rows;
            } else {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie[$field], intval($entrie['ncount']), $language];
                }
                $filename = 'Report_data_' . $this->getContestIdPart() . '_ON_FIELD_' . $field . $this->getFromPart();
                return $this->arrayToCsvDownload($rows, $filename . ".csv");
            }
        }
    }

    function getReportDataGroupedByPeriod ($period_format, $format = "graph")
    {
        $rows = [];
        if ('graph' !== $format) {
            $rows[] = ['day', 'ncount', 'language'];
        }
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $select = \Drupal::database()->select('pepsicontest_reg_contest', 'c');
            $select->addExpression('COUNT(c.user_id)', 'ncount');
            $select->addExpression("DATE_FORMAT(c.enterdate," . $period_format . ")", 'day');

            $select->condition('c.contest_id', $this->contestid);
            $select->condition('c.regdate', $this->startdate, '>=');
            $select->condition('c.regdate', $this->enddate, '<=');
            $select->condition('c.language', $language);
            if (is_string($period_format)) {
                $select->groupBy("day");
            } else {
                foreach ($period_format as $current_period) {
                    $select->groupBy("DATE_FORMAT(c.enterdate," . $current_period . ")");
                }
            }
            $select->orderBy("day", "ASC");
            $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
            if ($format === 'graph') {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie['day'], intval($entrie['ncount'])];
                }
                return $rows;
            } else {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie['day'], intval($entrie['ncount']), $language];
                }
                $filename = 'ReportEntriesGroupedByDay' . $this->getContestIdPart() . $this->getFromPart();
                return $this->arrayToCsvDownload($rows, $filename . ".csv");
            }
        }
    }

    function getReportEntriesCountByAgeAndGender ( $format = "graph")
    {
        $rows = [];
        if ('graph' !== $format) {
            $rows[] = ['age' ,'gender', 'ncount', 'language'];
        }
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $select = \Drupal::database()->select('pepsicontest_reg_contest', 'c');
            $select->addExpression('COUNT(*)', 'ncount');
            $select->addField('c', 'gender');
            $select->addExpression('FLOOR(datediff(now(), ufbday.field_bday_value) / 365.25)', 'age');
            $select->leftJoin('user__field_bday', 'ufbday', 'c.user_id=ufbday.entity_id');
            $select->condition('c.contest_id', $this->contestid);
            $select->condition('c.regdate', $this->startdate, '>=');
            $select->condition('c.regdate', $this->enddate, '<=');
            $select->condition('c.language', $language);
            $select->groupBy("age");
            $select->groupBy("c.gender");
            $select->orderBy("ncount", "DESC");
            $select->orderBy("age", "ASC");
            $select->orderBy("gender", "ASC");
            $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

            if ($format === 'graph') {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie['age'] . ' years old, gender: ' . $entrie['gender'], intval($entrie['ncount']), $language];
                }
                return $rows;
            } else {
                foreach ($entries as $entrie) {
                    $rows[] = [intval($entrie['age']), $entrie['gender'], intval($entrie['ncount']), $language];
                }
                $filename = 'ReportEntriesCountByAgeAndGender' . $this->getContestIdPart(). $this->getFromPart();
                return $this->arrayToCsvDownload($rows, $filename . ".csv");
            }
        }
    }

    function getReportParticipantCountByAgeAndGender ($format = "graph")
    {
        $rows = [];
        if ('graph' !== $format) {
            $rows[] = ['age', 'gender', 'ncount', 'language'];
        }
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $select = \Drupal::database()->select('pepsicontest_reg_contest', 'c');
            $select->addExpression('COUNT(DISTINCT(c.user_id))', 'ncount');
            $select->addField('c', 'gender');
            $select->addExpression('FLOOR(datediff(now(), ufbday.field_bday_value) / 365.25)', 'age');
            $select->leftJoin('user__field_bday', 'ufbday', 'c.user_id=ufbday.entity_id');
            $select->condition('c.contest_id', $this->contestid);
            $select->condition('c.regdate', $this->startdate, '>=');
            $select->condition('c.regdate', $this->enddate, '<=');
            $select->condition('c.language', $language);
            $select->groupBy("age");
            $select->groupBy("gender");
            $select->groupBy("c.user_id");

            $select->orderBy("ncount", "DESC");
            $select->orderBy("age", "ASC");
            $select->orderBy("gender", "ASC");
            $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
            if ($format === 'graph') {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie['age'] . ' years old, gender: ' . $entrie['gender'], intval($entrie['ncount']), $language];
                }
                return $rows;
            } else {
                foreach ($entries as $entrie) {
                    $rows[] = [intval($entrie['age']), $entrie['gender'], intval($entrie['ncount']), $language];
                }
                $filename = 'ReportParticipantCountByAgeAndGender' . $this->getContestIdPart() . $this->getFromPart();
                return $this->arrayToCsvDownload($rows, $filename . ".csv");
            }
        }
    }

    function getReportDataByDate($format = "graph")
    {
        $rows = [];
        if ('graph' !== $format) {
            $rows[] = ['regdate', 'ncount', 'language'];
        }
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $select = \Drupal::database()->query("SELECT SUBSTRING(regdate,1,10) AS regdate, "
                . "COUNT(*) as ncount FROM pepsicontest_reg_contest "
                . "WHERE contest_id = $this->contestid "
                . "AND regdate >= '$this->startdate' "
                . "AND regdate <= '$this->enddate' "
                . "AND language = '" . $language . "' "
                . "GROUP BY SUBSTRING(regdate,1,10)");

            $entries = $select->fetchAll();
            if ($format === 'graph') {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie->regdate, intval($entrie->ncount)];
                }
                return $rows;
            } else {
                foreach ($entries as $entrie) {
                    $rows[] = [$entrie->regdate, intval($entrie->ncount), $language];
                }
            }
        }
        if ('graph' != $format) {
            $filename = 'ReportDataByDatefor' . $this->getContestIdPart() . $this->getFromPart();
            return $this->arrayToCsvDownload($rows, $filename . ".csv");
        }
    }

    function getReportAnswer($format = "graph")
    {
        $rows = [];
        if ('graph' !== $format) {
            $rows[] = ['question', 'answer', 'ncount', 'language'];
        }
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            try {
                $query = "SELECT sub.field_subject_value AS question, ans.field_answer_value AS answer,COUNT(*) AS ncount
                                                FROM pepsicontest_reg_answer rans,
                                                     pepsicontest_reg_contest reg,
                                                     node__field_questions q,
                                                     node__field_answer ans,
                                                     node__field_subject sub
                                                WHERE rans.contest_id = $this->contestid
                                                AND reg.contest_id = rans.contest_id
                                                AND reg.user_id = rans.user_id
                                            AND reg.regdate >= '$this->startdate' 
                                            AND reg.regdate <= '$this->enddate' 
                                            AND rans.question_id = $this->questionid
                                            AND q.langcode = '$language'
                                            AND q.entity_id = rans.contest_id
                                            AND ans.langcode = '$language'	
                                            AND ans.entity_id = rans.question_id
                                            AND ans.delta = rans.answer
                                            AND sub.langcode = '$language'
                                            AND sub.entity_id = rans.question_id
                                        GROUP BY sub.field_subject_value, ans.field_answer_value";
                $select = \Drupal::database()->query($query);
                $entries = $select->fetchAll();
            } catch (\Exception $e) {
                error_log('error=' . $e->getMessage() . ' & query = ' . preg_replace('/\s+/S', " ", $query));
            }
            $question = '';

            if ($format === 'graph') {
                foreach ($entries as $entry) {
                    $question = $entry->question;
                    $rows[] = [$entry->answer, intval($entry->ncount)];
                }
                return ['question' => $question, 'rows' => $rows];
            } else {
                foreach ($entries as $entry) {
                    $rows[] = [$entry->question, $entry->answer, intval($entry->ncount), $language];
                }
            }
        }
        if ('graph' != $format) {
            $filename = 'ReportAnswerfor' . $this->getContestIdPart() . $this->getFromPart();
            return $this->arrayToCsvDownload($rows, $filename . ".csv");
        }
    }

    public function getReportPollAnswersGroupedByDay()
    {
        $rows = [];
        $rows[] = ['poll_id', 'question', 'vote_date', 'ncount', 'language'];
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $query = "SELECT  distinct(p.id) as poll_id, 
                                                          q.question , 
                                                          DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d') as vote_date, 
                                                          count(pid) as ncount  
                                                  FROM 
                                                    poll p 
                                                  LEFT JOIN 
                                                    poll_field_data q 
                                                  ON 
                                                    p.id=q.id 
                                                  LEFT JOIN 
                                                    poll_vote v 
                                                  ON 
                                                    p.id=v.pid 
                                                  WHERE
                                                    DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d') >= '" . substr($this->startdate,0,10) . "' 
                                                  AND 
                                                    DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d') <= '" . substr($this->enddate,0,10). "'
                                                  AND
                                                    q.langcode = '" .$language."' 
                                                  GROUP BY
                                                    p.id,
                                                    DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d'),
                                                    q.question
                                                  ORDER BY  
                                                    p.id ASC, 
                                                    DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d') ASC, 
                                                    q.question ASC
                                                    ";
            $select = \Drupal::database()->query($query);

            $entries = $select->fetchAll();
            foreach ($entries as $entry) {
                $rows[] = [$entry->poll_id, $entry->question , $entry->vote_date, $entry->ncount, $language];
            }
        }
        $filename = 'ReportPollAnswersGroupedByDay' . $this->getFromPart();
        return $this->arrayToCsvDownload($rows, $filename . ".csv");
    }

    public function getReportPollChoicesParticipationCount()
    {
        $rows = [];
        $rows[] = ['poll_id', 'question', 'choice', 'vote_date', 'ncount', 'language'];
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $lang_arr = array(  "en" => array(  'langcode' => 'en',
                'default_langcode' => 1),
                'fr' => array(  'langcode' => 'fr',
                    'default_langcode' => 0)
            );
            $query = "SELECT 
                                                p.id as poll_id,
                                                c.id,
												DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d') as vote_date,
                                                count(pid) as ncount
                                              FROM 
                                                poll p 
                                              LEFT JOIN 
                                                poll__choice pc 
                                              ON 
                                                p.id = pc.entity_id 
                                              LEFT JOIN 
                                                poll_choice_field_data c 
                                              ON 
                                                pc.choice_target_id=c.id 
                                              LEFT JOIN 
                                                poll_field_data q 
                                              ON 
                                                p.id=q.id 
                                              LEFT JOIN 
                                                poll_vote v 
                                              ON 
                                                p.id=v.pid 
                                              AND 
                                                c.id=v.chid 
                                              WHERE
                                                (DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d') >= '" . substr($this->startdate, 0, 10) . "' 
                                              AND 
                                                DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d') <= '" . substr($this->enddate, 0, 10) . "')
                                              GROUP BY
                                                c.id,
                                                DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d'),
                                                p.id
                                              ORDER BY 
                                                p.id ASC, 
                                                c.id ASC, 
                                                DATE_FORMAT(FROM_UNIXTIME(v.timestamp), '%Y-%m-%d') ASC";
            $query2 = "  SELECT DISTINCT(c.id) as id, q.question, choice FROM poll p 
                                              LEFT JOIN 
                                                poll__choice pc 
                                              ON 
                                                p.id = pc.entity_id 
                                              LEFT JOIN 
                                                poll_choice_field_data c 
                                              ON 
                                                pc.choice_target_id=c.id 
                                              LEFT JOIN 
                                                poll_field_data q 
                                              ON 
                                                p.id=q.id 
                                              LEFT JOIN 
                                                poll_vote v 
                                              ON 
                                                p.id=v.pid 
                                              AND 
                                                c.id=v.chid
                                              WHERE
                                                c.langcode ='" . $lang_arr[$language]['langcode'] . "'
                                              AND 
                                                q.default_langcode= " . $lang_arr[$language]['default_langcode'] . "
                                               ORDER BY
                                                c.id ASC";
            $select = \Drupal::database()->query($query);
            $select2 = \Drupal::database()->query($query2);
            $entries = $select->fetchAll();
            $question_data = $select2->fetchAll(\PDO::FETCH_ASSOC);
            $question_arr = [];
            foreach ($question_data as $question_row) {
                $question_arr[$question_row['id']] = $question_row;
            }
            foreach ($entries as $entry) {
                $rows[] = [$entry->poll_id, $question_arr[$entry->id]['question'], $question_arr[$entry->id]['choice'], $entry->vote_date, $entry->ncount, $language];
            }
        }
        $filename = 'ReportPollChoicesParticipationCount' . $this->getFromPart();
        return $this->arrayToCsvDownload($rows, $filename . ".csv");
    }

    public function getReportContestParticipationData($intermediary = true, $by_sex = true)
    {
        $contest_data = $this->getCurrentContestData();
        $global_rows = [];
        $global_rows = $this->getReportContestsUniqueParticipationByDate($intermediary, $by_sex, $global_rows);

        $global_rows = $this->getReportContestsTotalParticipationByDate($intermediary, $by_sex, $global_rows);
        ksort($global_rows[0]);
        $filename = substr(__FUNCTION__, 3) . $this->getFromPart();
        return $this->arrayToCsvDownload($global_rows, $filename . ".csv");
    }

    public function getCurrentContestData()
    {
        $query = "SELECT 
                        DISTINCT(p.contest_name),
                        o.field_opening_date_value,
                        c.field_closing_date_value
                    FROM
                      pepsicontest_reg_contest p 
                    LEFT JOIN
                      node__field_closing_date c
                    ON 
                      p.contest_id = c.entity_id 
                    LEFT JOIN
                      node__field_opening_date o
                    ON 
                      p.contest_id = o.entity_id   
                    WHERE
                        p.contest_id = " . $this->contestid . "  
                    ";

        $select = \Drupal::database()->query(preg_replace('/\s+/S', " ", $query));
        $entries = $select->fetchAll();
        foreach ($entries as $entry) {
            $this->startdate = substr($entry->field_opening_date_value, 0, 10);
            $this->enddate = substr($entry->field_closing_date_value, 0, 10);
        }
    }

    public function getReportContestsUniqueParticipationByDate($intermediary = false, $by_sex = false, $global_rows = false, $unique = true)
    {
            return $this->getReportContestsParticipations(__FUNCTION__, $unique, $by_sex, $intermediary, $global_rows);
    }

    public function getReportContestsTotalParticipationByDate($intermediary = false, $by_sex = false, $global_rows = false, $unique = false)
    {
        return $this->getReportContestsParticipations(__FUNCTION__, $unique, $by_sex, $intermediary, $global_rows);
    }

    public function getReportContestsParticipations($current_function_caller, $unique = false, $by_sex = false, $intermediary = false, $global_rows = false)
    {
        if ($global_rows !== FALSE) {
            $rows = &$global_rows;
        }
        $gender_loop = array('');
        $ncount_var = 'ncount';
        $extra_query_fields = 'count(user_id) as ';
        if (!$by_sex) {
            $rows = [];
        } else {
            $gender_loop = array('', ',gender');
        }

        if ($unique) {
            $ncount_var = 'nCountUnique';
            $extra_query_fields = 'count(distinct(user_id)) as ';
        }
        $extra_where = '';
        if ($intermediary) {
            $extra_where = ' AND contest_id = ' . $this->contestid;
        }
        $extra_query_fields .= $ncount_var . ',';
        if (!$by_sex) {
            $rows[] = array('contest_id' => 'contest_id', 'contest_name' => 'contest_name', $ncount_var => $ncount_var, 'language' => 'language');
        } else {
            if (empty($rows[0])) {
                $rows[0] = array('startdate' => 'startdate', 'enddate' => 'enddate', 'contest_id' => 'contest_id', 'contest_name' => 'contest_name', 'language' =>'language', $ncount_var => $ncount_var);
            }
        }
        $this->checkLang();
        $cnt = 1;
        foreach ($this->lang_arr as $language) {
            foreach ($gender_loop as $gender_group_by) {
                $query = "SELECT
                        " . $extra_query_fields . " 
                        MIN(contest_id) as min_contest_id, 
                        MIN(contest_name) as min_contest_name
                        " . $gender_group_by . "
                      FROM 
                        `pepsicontest_reg_contest` 
                      where 
                        regdate >= '" . $this->startdate . "' 
                      and 
                        regdate <= '" . $this->enddate . "'
                      and
                        language = '" . $language . "'
                      " . $extra_where . "
                      group by
                        contest_id " . $gender_group_by . "
                       order by contest_id ASC";
                if ("" != $gender_group_by) {
                    $query .= $gender_group_by . ' ASC';
                }
                $query = preg_replace('/\s+/S', " ", $query);
                $select = \Drupal::database()->query($query);


                if ("" != $gender_group_by) {
                    $lang_entries[$language] = $select->fetchAll(\PDO::FETCH_ASSOC);
                    if (count($lang_entries) != count($this->lang_arr)) {
                        //error_log('###continuing'.'count($lang_entries)'.count($lang_entries).'count($this->lang_arr)'.count($this->lang_arr));
                        $wait_before_assignemnt = true;
                        continue;
                    } else {
                        $wait_before_assignemnt = false;
                    }
                } else {
                    $wait_before_assignemnt = true;
                    $entries = $select->fetchAll(\PDO::FETCH_ASSOC);
                    //error_log('###setting all, query='.$query);
                    $this->forLoopContest($rows, $cnt, $entries, $by_sex, $gender_group_by, $language, $ncount_var);
                }
                if (!$wait_before_assignemnt && (isset($lang_entries['fr']) || isset($lang_entries['en']))) {
                    $count_fr = isset($lang_entries['fr']) ? count($lang_entries['fr']) : 0;
                    $count_en = isset($lang_entries['en']) ? count($lang_entries['en']) : 0;
                    if (isset($lang_entries['fr']) && isset($lang_entries['en'])) {
                        $ordered_entries = array('fr' => $lang_entries['fr'], 'en' => $lang_entries['en']);
                        if ($count_en >= $count_fr && isset($lang_entries['fr']) && isset($lang_entries['en'])) {
                            $ordered_entries = array('en' => $lang_entries['en'], 'fr' => $lang_entries['fr']);
                        }
                    } else {
                        $ordered_entries = $lang_entries;
                    }
                    foreach ($ordered_entries as $language => $entry_lang) {
                        $this->forLoopContest($rows, $cnt, $entry_lang, $by_sex, $gender_group_by, $language, $ncount_var);
                    }
                }
            }
        }
        if (!$intermediary) {
            ksort($rows[0]);
            $filename = substr($current_function_caller, 3) . $this->getFromPart();
            return $this->arrayToCsvDownload($rows, $filename . ".csv");
        } else {
            return $rows;
        }

    }
    public function generateCountVarGender($ncount_var, $entry)
    {
        if (isset($entry['gender'])) {
            $tmp_value = $ncount_var . '_' . $entry['gender'];

        } else {
            $tmp_value = $ncount_var . '_' . $entry;
        }
        return $tmp_value;
    }

    public function forLoopContest(&$rows, &$cnt, $entries, $by_sex, $gender_group_by, $language, $ncount_var)
    {
        $gender_str = $tmp_gender_str = 'M,F,O,H,';
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if (!$by_sex) {
                    $tmp_array = array('contest_id' => $entry['min_contest_id'], 'contest_name' => $entry['min_contest_name'], 'language' => $language, $ncount_var => $entry[$ncount_var]);
                    $rows[] = $tmp_array;
                } else {
                    if (!isset($entry['gender'])) {
                        if (!isset($rows[$language])) {
                            $rows[$language] = array('startdate' => $this->startdate, 'enddate' =>  $this->enddate, 'contest_id' =>  $entry['min_contest_id'], 'contest_name' => $entry['min_contest_name'], 'language' => $language);
                        }
                        if (!in_array($ncount_var, $rows[0], true)) {
                            $rows[0][$ncount_var] = $ncount_var;
                        }

                        $rows[$language][$ncount_var] = $entry[$ncount_var];
                    } else {
                        $the_current_n_count = $this->generateCountVarGender($ncount_var, $entry);
                        if (!isset($rows[0][$the_current_n_count])) {
                            $rows[0][$the_current_n_count] = $the_current_n_count;
                        }
                        $rows[$language][$the_current_n_count] = $entry[$ncount_var];
                        $offset = stripos($tmp_gender_str, $entry['gender']);
                        if ($offset !== FALSE) {
                            $tmp_gender_str = substr($tmp_gender_str, 0, $offset) . substr($tmp_gender_str, ($offset+2));
                        }
                    }
                }
            }
            if ($by_sex) {
                if ("" != $tmp_gender_str) {
                   do {
                       $tmp_gender = substr($tmp_gender_str, 0, 1);
                       $the_current_n_count = $this->generateCountVarGender($ncount_var, $tmp_gender);
                       if (!isset($rows[0][$the_current_n_count])) {
                           $rows[0][$the_current_n_count] = $the_current_n_count;
                       }
                       $rows[$language][$the_current_n_count] = 0;
                       $tmp_gender_str = substr($tmp_gender_str, 2);
                   } while ("" != $tmp_gender_str);
                }
            }
            if (isset($rows[$language])) {
                ksort($rows[$language]);
            } else {
                ksort($rows[(count($rows)-1)]);
            }

            $cnt++;
        }
    }
    /*
    public function getReportContestsUniqueParticipationByDate()
    {
        $rows = [];
        $rows[] = ['contest_id', 'contest_name', 'ncount', 'language'];
        $this->checkLang();
        foreach ($this->lang_arr as $language) {
            $select = \Drupal::database()->query("SELECT
                                                MIN(contest_id) as min_contest_id,
                                                MIN(contest_name) as min_contest_name,
                                                count(distinct(user_id)) as ncount
                                              FROM
                                                `pepsicontest_reg_contest`
                                              where
                                                regdate >= '" . $this->startdate . "'
                                              and
                                                regdate <= '" . $this->enddate . "'
                                              and
                                                language = '" . $language . "'
                                              group by
                                                contest_id
                                               order by contest_id ASC");
            $entries = $select->fetchAll();
            foreach ($entries as $entry) {
                $rows[] = [$entry->min_contest_id, $entry->min_contest_name, $entry->ncount, $language];
            }
        }
        $filename = 'ReportContestsUniqueParticipationByDate' . $this->getFromPart();
        return $this->arrayToCsvDownload($rows, $filename . ".csv");
    }*/

    function pickwinner($limit = 50, $format = 'csv')
    {
        $winners_to_pick = 10;
        $double_the_chances = false;
        //$this->updateWinnersListWithOptins($format, $double_the_chances);

        try {
            $query = "SELECT  DISTINCT contest_id, user_id, contest_name, first_name, last_name, email, province, language, enterdate, postalcode
                          FROM pepsicontest_reg_contest 
                          WHERE contest_id = $this->contestid  
                            AND enterdate>='$this->startdate' 
                            AND enterdate<='$this->enddate' 
                          ORDER BY RAND()
                          LIMIT " . $limit;
            $select = \Drupal::database()->query($query);
        } catch (\Exception $e) {
            error_log('error='.$e->getMessage() . ' & query = '.preg_replace('/\s+/S', " ", $query));
        }
        $entries = $select->fetchAll();
        
        $rows = [];
        $rows[] = ['contest_id', 'user_id' , 'contest_name', 'first_name', 'last_name', 'email', 'province', 'language', 'regdate', 'postalcode'];
        foreach ($entries as $entry) {
                $rows[] = [$entry->contest_id, $entry->user_id, $entry->contest_name, $entry->first_name, $entry->last_name, $entry->email, $entry->province, $entry->language, $entry->enterdate, $entry->postalcode];
        }
        
        $filename = 'WinnersList' . $this->getContestIdPart() . $this->getFromPart();
        return $this->arrayToCsvDownload($rows, $filename . ".csv");
    }

    public function arrayToCsvDownload($array, $filename = "export.csv", $delimiter=";")
    {

        $absolute_file_path = $_SERVER['DOCUMENT_ROOT'];
        $relative_file_path = '/sites/default/files/csvfiles/';
        
        if (!is_dir($absolute_file_path . $relative_file_path)) {
            mkdir($absolute_file_path . $relative_file_path, 0777, true);
        }
        
        $f = fopen($absolute_file_path . $relative_file_path . $filename, 'w');
        
        foreach ($array as $line) {
            foreach ($line as &$val) {
                if ('UTF-8' === strtoupper(mb_detect_encoding($val))) {
                    $val = mb_convert_encoding($val, 'iso-8859-1', 'UTF-8');
                }
            }
            // generate csv lines from the inner arrays
            fputcsv($f, $line, $delimiter);
        }
        fclose($f);
        return $relative_file_path.$filename;
    }

    public function getContestIdPart()
    {
        return 'for_contestId_' . $this->contestid;
    }

    public function getFromPart()
    {
        return '_from' . substr($this->startdate,0,10) . '_TO_' .substr($this->enddate,0,10);
    }

    public function checkLang()
    {
        if ('en' == $this->language || 'fr' == $this->language) {
            $this->lang_arr = array($this->language);
        } else {
            $this->lang_arr = array('en', 'fr');
        }
    }

    public function reportSourceID(Request $request){

        $template = "sourceid_report";
        $data = [];
        $status = '';
        $data['title'] = "SourceID report";

        $data['sourceids'] = [];
        $data['countries'] = ['canada', 'usa'];

        if ($request->getMethod() == 'POST'){
            // $contest_id = $request->request->get('contestid');
            $start_date = $request->request->get('start_date'); 
            $end_date = $request->request->get('end_date') ;
            
            $data['selected_country'] = $request->get('country');
            $data['start_date'] = $start_date;
            $data['end_date'] = $end_date;
            $start_date .= " 01:00:00";
            $end_date .= " 23:59:59";

            $obj = new ReportGenerator();
            $data['sourceids'] =  $obj->getSourceidByDate($start_date, $end_date, $data['selected_country']);

        }



        return array(
            '#theme' => $template,
            '#data'  => $data,
        );

    }
}
