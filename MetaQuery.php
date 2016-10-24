<?php
/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Bundle\WfdMetaBundle;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Eine Abfrage von wfd_meta-Daten, die vorher für bestimmte Tabellen oder
 * Doctrine ORM-Entitätsklassen konfiguriert werden kann.
 */
class MetaQuery
{

    /** @var Provider */
    protected $provider;

    /** @var ContainerInterface */
    protected $container;

    protected $tables = array();

    /**
     * @var string[]
     */
    protected $classnames = array();

    /**
     * MetaQuery constructor.
     * @param Provider $provider
     * @param ContainerInterface $container
     */
    public function __construct(Provider $provider, ContainerInterface $container)
    {
        $this->provider = $provider;
        $this->container = $container;
    }

    /**
     * Fügt eine einzelne Doctrine-Entität in die wfdmeta-Abfrage ein.
     *
     * Wegen BC: Fängt FQCNs und Arrays von FCQNs ab und übergibt sie an addEntityClass() bzw. addEntityClasses(), da
     * vorher addEntity() für diese Funktionalität benutzt wurde.
     *
     * @param object|string|string[] $entity
     */
    public function addEntity($entity)
    {
        if (is_string($entity)) {
            @trigger_error("Passing FQCNs to addEntity() is deprecated; use addEntityClass() instead.", E_USER_DEPRECATED);
            $this->addEntityClass($entity);
            return;
        }

        if (is_array($entity) && is_string($entity[0])) {
            @trigger_error("Passing an array of FQCNs to addEntity() is deprecated; use addEntityClasses() instead.", E_USER_DEPRECATED);
            $this->addEntityClasses($entity);
            return;
        }
    }

    /**
     * Fügt alle Entitäten der gegebenen Klasse in die wfdmeta-Abfrage ein.
     *
     * @param string $classname Vollqualifizierter Klassenname
     */
    public function addEntityClass($classname)
    {
        $this->addEntityClasses([$classname]);
    }

    /**
     * Fügt alle Entitäten der gegebenen Klassen in die wfdmeta-Abfrage ein.
     *
     * @param string[] $classnames Array von vollqualifizierten Klassennamen
     */
    public function addEntityClasses(array $classnames)
    {
        $this->classnames = array_unique(array_merge($this->classnames,$classnames));
    }

    private function setupTablesForEntities()
    {
        if ($this->classnames) {
            /** @var EntityManager $em */
            $em = $this->container->get('doctrine.orm.entity_manager');

            foreach ($this->classnames as $classname) {
                try {
                    $meta = $em->getClassMetadata($classname);
                    if (!$meta->isInheritanceTypeNone()) {
                        $meta = $em->getClassMetadata($meta->rootEntityName);
                    }
                    $this->addTable($meta->getTableName());
                } catch (MappingException $e) {
                    throw new \RuntimeException("webfactory/wfdmeta-bundle: Ein MetaQuery soll für die Klasse '$classname' konfiguriert werden, die keine bekannte Doctrine-Entität ist.",
                        0, $e);
                }
            }
        }

        $this->classnames = array();
    }

    public function addTable($tableName)
    {
        foreach ((array)$tableName as $t) {
            $this->tables[$t] = true;
        }
    }

    public function getLastTouched()
    {
        $this->setupTablesForEntities();
        return $this->provider->getLastTouched(array_keys($this->tables));
    }
}
