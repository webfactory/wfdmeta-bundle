# CHANGELOG for WebfactoryWfdMetaBundle

## Version 3.0

* Removed internal caching in the `Provider` class.  
* Added the custom `ConfigCacheFactory` implementation and new WfdMetaResource types like `DoctrineEntityClassResource` or `WfdTableResource`. Add instances of these resource to `RouteCollection` and `MessageCatalogue` instances to track changes.
* Removed `RefreshingRouter` and `RefreshingTranslator` classes, `webfactory_wfd_meta.refreshing_router` and `webfactory_wfd_meta.refreshing_translator` services and the `webfactory_wfd_meta.refresh_router` and `webfactory_wfd_meta.refresh_translator` configuration keys.
* Made the `webfactory_wfd_meta.doctrine_metadata_helper` service private. It is not considered part of this bundle's public API.


## Version 2.6.0

* Added a new `MetadataFacade`

## Version 2.4

* Added the `webfactory_wfd_meta.controller.template` controller service to render static templates with `wfd_meta` based cache validation

## Version 2.3

* Added the `resetInterval` setting in the `Send304IfNotModified` annotation

## Version 2.2

* BC break: Renamed `webfactory.wfd_meta.provider` service to `webfactory_wfd_meta.provider`
* Added the `MetaQuery` class
* Allow usage of Doctrine entity class FQCNs in the `Send304IfNotModified` annotation