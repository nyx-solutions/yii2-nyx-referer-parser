<?php

    namespace nox\components\http\referer;

    use nox\components\http\userAgent\UserAgentParser;
    use nox\helpers\StringHelper;
    use Snowplow\RefererParser\Parser;
    use Snowplow\RefererParser\Referer;

    /**
     * Class RefererParser
     *
     * @package nox\components\http\referer
     */
    class RefererParser extends Parser
    {
        #region Constants
        const TYPE_GOOGLE_ORGANIC  = 1;
        const TYPE_GOOGLE_ADS      = 2;
        const TYPE_GOOGLE_CAMPAIGN = 3;
        const TYPE_EXTERNAL_SEARCH = 4;
        const TYPE_EXTERNAL_SITE   = 5;
        const TYPE_DIRECT          = 6;
        #endregion

        #region Initialization
        /**
         * @var Referer
         */
        public $parsedUrl;

        /**
         * @var bool
         */
        protected $ready = false;

        /**
         * @return void
         */
        public function isReady()
        {
            if (!(bool)$this->ready) {
                $this->parsedUrl = $this->parse(\Yii::$app->request->referrer, \Yii::$app->request->url);
            }
        }
        #endregion

        #region Source Detection
        /**
         * @return bool
         */
        public function isGoogleOrganic()
        {
            $this->isReady();

            if (!$this->isGoogleCampaign() && !$this->isGoogleAds() && StringHelper::convertCase($this->parsedUrl->getMedium(), StringHelper::CASE_LOWER) == 'search' && StringHelper::convertCase($this->parsedUrl->getSource(), StringHelper::CASE_LOWER) == 'google') {
                return true;
            }

            return false;
        }

        /**
         * @return bool
         */
        public function isGoogleAds()
        {
            $this->isReady();

            $gclid = \Yii::$app->request->get('gclid', '');

            if (!empty($gclid)) {
                return true;
            }

            return false;
        }

        /**
         * @return bool
         */
        public function isGoogleCampaign()
        {
            $this->isReady();

            $source   = \Yii::$app->request->get('utm_source', '');
            $medium   = \Yii::$app->request->get('utm_medium', '');
            $campaign = \Yii::$app->request->get('utm_campaign', '');

            if (!empty($source) && !empty($medium) && !empty($campaign)) {
                return true;
            }

            return false;
        }

        /**
         * @return bool
         */
        public function isExternalSearch()
        {
            $this->isReady();

            if (!$this->isGoogleOrganic() && !$this->isGoogleCampaign() && !$this->isGoogleAds() && $this->parsedUrl->isKnown()) {
                return true;
            }

            return false;
        }

        /**
         * @return bool
         */
        public function isExternalUnknownSource()
        {
            $this->isReady();

            $referrer = \Yii::$app->request->referrer;

            if (!empty($referrer)) {
                $referrerHost = parse_url($referrer);
                $currentHost  = parse_url(\Yii::$app->request->hostInfo);

                if (is_array($referrerHost) && is_array($currentHost) && isset($referrerHost['host'], $currentHost['host']) && $referrerHost['host'] == $currentHost['host']) {
                    $referrer = null;
                }
            }

            if (!$this->isGoogleOrganic() && !$this->isGoogleCampaign() && !$this->isGoogleAds() && !$this->isExternalSearch() && !empty($referrer)) {
                return true;
            }

            return false;
        }

        /**
         * @return bool
         */
        public function isDirect()
        {
            $this->isReady();

            if (!$this->isGoogleOrganic() && !$this->isGoogleCampaign() && !$this->isGoogleAds() && !$this->isExternalSearch() && !$this->isExternalUnknownSource()) {
                return true;
            }

            return false;
        }
        #endregion

        #region Collect Data
        /**
         * @return integer
         */
        public function getType()
        {
            if ($this->isGoogleOrganic()) {
                return self::TYPE_GOOGLE_ORGANIC;
            } elseif ($this->isGoogleAds()) {
                return self::TYPE_GOOGLE_ADS;
            } elseif ($this->isGoogleCampaign()) {
                return self::TYPE_GOOGLE_CAMPAIGN;
            } elseif ($this->isExternalSearch()) {
                return self::TYPE_EXTERNAL_SEARCH;
            } elseif ($this->isExternalUnknownSource()) {
                return self::TYPE_EXTERNAL_SITE;
            } else {
                return self::TYPE_DIRECT;
            }
        }

        /**
         * @return null|string
         */
        public function getSource()
        {
            if ($this->isGoogleOrganic()) {
                return 'orgânico';
            } elseif ($this->isGoogleAds()) {
                return 'google-cpc';
            } elseif ($this->isGoogleCampaign()) {
                return (string)\Yii::$app->request->get('utm_source', '');
            } elseif ($this->isExternalSearch()) {
                return 'busca-orgânica-externa';
            } elseif ($this->isExternalUnknownSource()) {
                return 'site-externo';
            } else {
                return 'direto';
            }
        }

        /**
         * @return null|string
         */
        public function getDescription()
        {
            if ($this->isGoogleOrganic()) {
                return 'Google Orgânico';
            } elseif ($this->isGoogleAds()) {
                return 'Google Ads';
            } elseif ($this->isGoogleCampaign()) {
                return 'Google Campanha';
            } elseif ($this->isExternalSearch()) {
                return $this->parsedUrl->getMedium().': '.$this->parsedUrl->getSource();
            } elseif ($this->isExternalUnknownSource()) {
                $sourceUrl = parse_url(\Yii::$app->request->referrer);
                $sourceUrl = $sourceUrl['scheme'].'://'.$sourceUrl['host'];

                return 'Site Externo: '.$sourceUrl;
            } else {
                return 'Acesso Direto';
            }
        }

        /**
         * @return null|string
         */
        public function getMedium()
        {
            if ($this->isGoogleCampaign()) {
                return (string)\Yii::$app->request->get('utm_medium', '');
            }

            return null;
        }

        /**
         * @return null|string
         */
        public function getTerm()
        {
            if ($this->isGoogleCampaign()) {
                return (string)\Yii::$app->request->get('utm_term', '');
            }

            return null;
        }

        /**
         * @return null|string
         */
        public function getContent()
        {
            if ($this->isGoogleCampaign()) {
                return (string)\Yii::$app->request->get('utm_content', '');
            }

            return null;
        }

        /**
         * @return null|string
         */
        public function getCampaign()
        {
            if ($this->isGoogleCampaign()) {
                return (string)\Yii::$app->request->get('utm_campaign', '');
            }

            return null;
        }

        /**
         * @return string
         */
        public function getDevice()
        {
            $device = UserAgentParser::parse();

            if (!$device->successfullyParsed) {
                return '';
            }

            return $device->platform.' - '.$device->browser.' '.$device->version;
        }
        #endregion

        #region Parser
        /**
         * @inheritdoc
         */
        public function parse($refererUrl, $pageUrl = null)
        {
            $this->ready = true;

            return parent::parse($refererUrl, $pageUrl);
        }
        #endregion
    }

