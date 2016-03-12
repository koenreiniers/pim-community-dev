# UPGRADE FROM 1.5 to 1.6

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## Catalog Bundle & Component

We've extracted following classes and interfaces from the Catalog bundle to the Catalog component:
 - validation

## Batch Bundle & Component

The Akeneo/BatchBundle has been introduced in the very first version of the PIM.

### In v1.5

Before the 1.5 it has been located in a dedicated Github repository and, due to that, it has not been enhanced as much as the other bundles.

It's a shame because this bundle provides the main interfaces and classes to structure the connectors for import/export.

To ease the improvements of this key part of the PIM, we moved the bundle in the pim-community-dev repository.

With the same strategy than for other old bundles, main technical interfaces and classes are extracted in a Akeneo/Batch component.

It helps to clearly separate its business logic and the Symfony and Doctrine "glue".

Has been done in 1.5:
 - move BatchBundle to pim-community-dev repository
 - extract main Step interface and classes
 - extract main Item interface and classes
 - extract main exceptions
 - extract main Event interface and classes
 - extract main Job interface and classes
 - extract domain models (doctrine entities) and move doctrine mapping to yml files
 - extract annotation validation in yml files (move also existing constraint from ImportExportBundle)
 - replace unit tests by specs, add missing specs
 - remove useless batch bundle files (composer, readme, upgrade, travis setup, etc)

### In v1.6

After the v1.5 re-work, several batch domain classes remain in the BatchBundle.

Several of these classes are deprecated, several others are not even used in the context of the PIM (we need extra analysis to know what to do with these).

Another remaining issue with the Batch component is the mix of concerns, batch logic, job and step configuration and step element UI configuration.

The Akeneo\Component\Batch\Step\StepInterface mixes lot of logic like getConfiguration(), setConfiguration() and getConfigurableStepElements() :
 - we extracted getConfiguration(), setConfiguration() in a ConfigurableInterface
 - we extracted getConfigurableStepElements() in a StepElementsContainerInterface and renamed the method getStepElements()

We can now define simple steps without elements or configuration.

If you implements a StepInterface that also requires configuration, you need to implements ConfigurableInterface (not come anymore by default from AbstractStep).

If you implements a StepInterface that also have step elements, you need to implements ConfigurableInterface (not come anymore by default from AbstractStep).

BC Breaks:
 - Remove getConfiguration() and setConfiguration() from Akeneo\Component\Batch\Step\AbstractStep
 - Remove getConfigurableStepElements from Akeneo\Component\Batch\Step\AbstractStep

The class Akeneo\Component\Batch\Step\ItemStep does not rely anymore on AbstractConfigurableStepElement to be able to create simpler StepElements implementing only NamedStepElementInterface for instance (new interface).

TODO:

The Akeneo\Component\Batch\Step\StepInterface should not assume the use of Akeneo\Component\Batch\Item\AbstractConfigurableStepElement.

Another issue in the Batch Bundle is the way the 'batch_jobs.yml' files are parsed and systematically stored in the DIC.

We could rely on a more standard way to define the batch services.

## Update dependencies and configuration

Download the latest [PIM community standard](http://www.akeneo.com/download/) and extract it:

```
 wget http://www.akeneo.com/pim-community-standard-v1.6-latest.tar.gz
 tar -zxf pim-community-standard-v1.6-latest.tar.gz
 cd pim-community-standard-v1.6.*/
```

Copy the following files to your PIM installation:

```
 export PIM_DIR=/path/to/your/pim/installation
 cp app/SymfonyRequirements.php $PIM_DIR/app
 cp app/config/config.yml $PIM_DIR/app/config/
 cp composer.json $PIM_DIR/
```

**In case your products are stored in Mongo**, don't forget to re-add the mongo dependencies to your *composer.json*:

```
 "doctrine/mongodb-odm-bundle": "3.0.1"
```

And don't forget to add your own dependencies to your *composer.json* in case you have some.

Merge the following files into your PIM installation:
 - *app/AppKernel.php*: TODO
 - *app/config/routing.yml*: TODO
 - *app/config/config.yml*: TODO

Then remove your old upgrades folder:
```
 rm upgrades/ -rf
```

Now you're ready to update your dependencies:

```
 cd $PIM_DIR
 composer update
```

This step will also copy the upgrades folder from `vendor/akeneo/pim-community-dev/` to your Pim project root to allow you to migrate.

Then you can migrate your database using:

```
 php app/console doctrine:migration:migrate
```

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query\\Filter\\DumperInterface/Pim\\Bundle\\CatalogBundle\\Command\\DumperInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query\\Filter\\AttributeFilterDumper/Pim\\Bundle\\CatalogBundle\\Command\\ProductQueryHelp\\AttributeFilterDumper/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query\\Filter\\FieldFilterDumper/Pim\\Bundle\\CatalogBundle\\Command\\ProductQueryHelp\\FieldFilterDumper/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query/Pim\\Component\\Catalog\\Query/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query/Pim\\Component\\Catalog\\Exception/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Exception/Pim\\Component\\Catalog\\Exception/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Event\\ProductEvents/Pim\\Component\\Catalog\\ProductEvents/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Repository/Pim\\Component\\Catalog\\Repository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Validator/Pim\\Component\\Catalog\\Validator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\AttributeType\\AttributeTypes/Pim\\Component\\Catalog\\AttributeTypes/g'
```
