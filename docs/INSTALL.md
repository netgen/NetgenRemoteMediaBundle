# Installation instructions for Netgen Remote Media Bundle

## Installation steps
  
* Configure the bundle:
    * in `config.yml` add basic configuration:
    ```
    netgen_remote_media:
        provider: cloudinary
        account_name: [your_cloud_name]
        account_key: [your_key]
        account_secret: [your_secret]
    ```

* Run the following from your website root folder:
    `$ composer require netgen/remote-media-bundle:^3.0`

* Activate the bundle:
    ```
    public function registerBundles()
    {
        ...
    
        $bundles[] = new Netgen\Bundle\RemoteMediaBundle\NetgenRemoteMediaBundle();
    
        return $bundles;
    }
    ```

* Clear the caches
    * run the following command:
    ```
    $ php bin/console cache:clear
    ```
