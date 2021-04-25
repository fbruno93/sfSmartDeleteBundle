# SmartDeleteBundle

Symfony bundle to avoid doctrine soft delete.

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that don't use Symfony Flex
#### Step 1: Add repository

Open a command console, enter your project directory and execute the
following command to add the repo of this bundle:

```sh
$ composer config repositories.foo vcs https://github.com/fbruno93/sfSmartDeleteBundle
```

#### Step 2: Download the Bundle
   
Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```sh
$ composer require bfy/smart-delete
```

#### Step 3: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// old.config.old/bundles.php

return [
    // ...
    Bfy\SmartDeleteBundle\SmartDeleteBundle::class => ['all' => true],
];
```

#### Step 4: SmartDelete configuration
````yaml
# config/packages/smart_delete.yaml
smart_delete:

  # The name of entity manager to use
  entity_manager:         deleted

  # The repository namespace
  repository_namespace:   App\Repository

  # Settings for EntityRow
  entity_row:

    # Folder to create EntityRow
    dir:                  '%kernel.project_dir%/src/Entity/Row'

    # Namespace of EntityRow
    prefix:               App\Entity\Row

  # Settings for EntityDeleted
  entity_deleted:

    # Folder to create EntityDeleted
    dir:                  '%kernel.project_dir%/src/Entity/Deleted'

    # Namespace of EntityDeleted
    prefix:               App\Entity\Deleted

  # Setting for main Entity
  entity_main:

    # Folder to create Entity
    dir:                  '%kernel.project_dir%/tests/Entity/Model'

    # Namespace of Entity
    prefix:               App\Entity\Model

    # Folder to save original doctrine entity
    backup:               '%kernel.project_dir%/entity_backup'
````

#### Step 5: Doctrine configuration
````yaml
# old.config.old/packages/doctrine.yaml
doctrine:
  dbal:
    default_connection: default # Set the name of default connection (here: default)

    connections:
      default: # The name of default connexions
        # your param for normal usage

      deleted: # It must be equal to smart_delete.entity_manager
        url: '%env(resolve:DELETED_DATABASE_URL)%'
        driver: 'pdo_sqlite'
        charset: utf8

    orm:
      default_entity_manager: default # Set the name of default entity manager (here: default)
      entity_managers:
        default: # The name of default entity manager 
          # your param for normal usage
        deleted:
          connection: deleted # ref to doctrine.dbal.connections
          mappings:
            Deleted:
              type: annotation

              # Folder of your deleted entities. It must be equal to smart_delete.entity_deleted.dir
              dir: '%kernel.project_dir%/src/Entity/Deleted' 
              
              # Namespace of your deleted entities. It must be equal to smart_delete.entity_deleted.prefix
              prefix: 'App\Entity\Deleted' 
````

#### Step 4: Read the doc 

it's this [way](src/Resources/doc/index.md)

## Todo
- [x] yaml : Set an entity manager name
- [x] yaml : Set a location for ``{Entity}Row``
- [x] yaml : Set a location for ``{Entity}Deleted``
- [x] yaml : Set a namespace for ``{Entity}Row``
- [x] yaml : Set a namespace for ``{Entity}Deleted``
- [x] dev : Use Filesystem symfony component instead of php file function (``fopen``, ``close``, ``fwrite``)
- [x] dev : Use yaml config instead php value 
- [x] cmd : Handle optional input parameter
- [x] test : Template
- [x] test : DeletedTrait
- [x] test : DeleteCommend
- [x] test : Doctrine Event subscriber
- [ ] doc : Write the doc
