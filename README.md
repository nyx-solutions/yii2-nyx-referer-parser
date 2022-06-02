Yii PHP Framework Version 2 / NYX Referer Parser
================================================

NYX Referer Parser is a PHP library for extracting marketing attribution data (such as search terms) from referer URLs.

[![Latest Stable Version](https://poser.pugx.org/nyx-solutions/yii2-nyx-referer-parser/v/stable)](https://packagist.org/packages/nyx-solutions/yii2-nyx-referer-parser)
[![Total Downloads](https://poser.pugx.org/nyx-solutions/yii2-nyx-referer-parser/downloads)](https://packagist.org/packages/nyx-solutions/yii2-nyx-referer-parser)
[![Latest Unstable Version](https://poser.pugx.org/nyx-solutions/yii2-nyx-referer-parser/v/unstable)](https://packagist.org/packages/nyx-solutions/yii2-nyx-referer-parser)
[![License](https://poser.pugx.org/nyx-solutions/yii2-nyx-referer-parser/license)](https://packagist.org/packages/nyx-solutions/yii2-nyx-referer-parser)
[![Monthly Downloads](https://poser.pugx.org/nyx-solutions/yii2-nyx-referer-parser/d/monthly)](https://packagist.org/packages/nyx-solutions/yii2-nyx-referer-parser)
[![Daily Downloads](https://poser.pugx.org/nyx-solutions/yii2-nyx-referer-parser/d/daily)](https://packagist.org/packages/nyx-solutions/yii2-nyx-referer-parser)
[![composer.lock](https://poser.pugx.org/nyx-solutions/yii2-nyx-referer-parser/composerlock)](https://packagist.org/packages/nyx-solutions/yii2-nyx-referer-parser)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

* Either run

```bash
php composer.phar require --prefer-dist "nyx-solutions/yii2-nyx-referer-parser" "*"
```

or add

```json
"nyx-solutions/yii2-nyx-referer-parser": "*"
```

to the `require` section of your application's `composer.json` file.

## Usage

In components (in the following example we get the RefererParser data and pass to the `PublicAccessLog` model, which in this case is just an example):

```php
namespace common\components\http;

use common\models\PublicAccessLog;

/**
 * Class RefererParser
 *
 * @package common\components\http
 */
class RefererParser extends \nyx\components\http\referer\RefererParser
{
    #region Constants
    const TYPE_GOOGLE_ORGANIC  = PublicAccessLog::TYPE_GOOGLE_ORGANIC;
    const TYPE_GOOGLE_ADS      = PublicAccessLog::TYPE_GOOGLE_ADS;
    const TYPE_GOOGLE_CAMPAIGN = PublicAccessLog::TYPE_GOOGLE_CAMPAIGN;
    const TYPE_EXTERNAL_SEARCH = PublicAccessLog::TYPE_EXTERNAL_SEARCH;
    const TYPE_EXTERNAL_SITE   = PublicAccessLog::TYPE_EXTERNAL_SITE;
    const TYPE_DIRECT          = PublicAccessLog::TYPE_DIRECT;
    #endregion
}
```

In the Controller Action:

```php
$refererParser = new RefererParser();

$this->publicAccessLog = new PublicAccessLog(['scenario' => PublicAccessLog::SCENARIO_INSERT]);

$this->publicAccessLog->type        = $refererParser->getType();
$this->publicAccessLog->description = $refererParser->getDescription();
$this->publicAccessLog->source      = $refererParser->getSource();
$this->publicAccessLog->medium      = $refererParser->getMedium();
$this->publicAccessLog->term        = $refererParser->getTerm();
$this->publicAccessLog->content     = $refererParser->getContent();
$this->publicAccessLog->campaign    = $refererParser->getCampaign();
$this->publicAccessLog->device      = $refererParser->getDevice();

$this->publicAccessLog->save(false);
```

## License

**yii2-nyx-referer-parser** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.

![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)
