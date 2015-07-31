<?php
/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Bundle\WfdMetaBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Webfactory\Bundle\WfdMetaBundle\MetaQuery;

/**
 * RefreshingTranslator ist wie Symfony\Bundle\FrameworkBundle\Translation\Translator mit der
 * Besonderheit, dass er zusätzlich den wfd_meta.last_touched-Timestamp berücksichtigt
 * und seinen Cache invalidiert, wenn darüber Änderungen der Datenbank bemerkt werden.
 *
 * Da wir nicht (wie beim RefreshingRouter) gezielt an den Code im parent::loadCatalogue ran kommen,
 * der den unzureichenden ConfigCache erzeugt, löschen wir hier einfach abhängig vom wfd_meta.last_touched-Timestamp
 * die Cache-Datei, da dann auch der normale ConfigCache invalide wird.
 */
class RefreshingTranslator extends BaseTranslator
{

    /** @var MetaQuery */
    protected $metaQuery;

    public function setMetaQuery(MetaQuery $metaQuery)
    {
        $this->metaQuery = $metaQuery;
    }

    public function addWfdTableDependency($tables)
    {
        trigger_error(
            'The addWfdTableDependency() setter is deprecated. Configure the MetaQuery instead.',
            E_USER_DEPRECATED
        );

        $this->metaQuery->addTable($tables);
    }

    protected function loadCatalogue($locale)
    {
        // Schauen, ob die Cache-Datei älter als wfd_meta.last_touched ist
        foreach ($this->getCacheFiles($locale) as $cacheFile) {
            if (filemtime($cacheFile) < $this->metaQuery->getLastTouched()) {
                @unlink($cacheFile);
            }
        }

        parent::loadCatalogue($locale);
    }

    /**
     * Returns a list of (existing) cache files that are related to the given locale.
     *
     * @param string $locale
     * @return string[]
     */
    protected function getCacheFiles($locale)
    {
        // Find cache files that contain a hash. These are generated by newer Symfony
        // versions (at least starting with 2.6).
        $paths = glob($this->options['cache_dir'] . '/catalogue.' . $locale . '.*.php');
        // Check the cache file that is generated by older Symfony versions (around 2.3).
        $cacheFile = $this->options['cache_dir'] . '/catalogue.' . $locale . '.php';
        if (is_file($cacheFile)) {
            $paths[] = $cacheFile;
        }
        return $paths;
    }
}
