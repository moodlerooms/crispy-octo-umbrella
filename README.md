# SimpleSAMLPHP IDP test image

This image is intended to be used as a test IDP environment. It has *not* been hardened for production use. It does not follow the Docker best practice of separating concerns. Instead it contains everything need to simply run SimpleSAMLPHP as an IDP. You can use this image to test your SAML and Shibboleth service providers (SP).
# How to use this image.

## Building the image
Clone this repository to a directory of your choice and then use docker build.
```
git clone <repourl> simplesamlphp
cd simplesamlphp
docker build . -t simplesamlphp
```

## Running the image
Add the following line to your /etc/hosts file:
```
127.0.0.1       simplesamlphp.test
```
Copy the contents of metadata-dist/ to metadata/ and optionally update those files (more on that later):
```console
$ cp metadata-dist/* metadata/
```
Then run the image:
```console
$ docker run -d -p 8080:80 -v /full/path/to/metadata/:/var/simplesamlphp/metadata/ simplesamlphp:latest
```
You should then be able to reach the SimpleSAMLPHP site by opening http://simplesamlphp.test:8080/simplesaml and login in as an admin with admin/password as the username/password.

## Configuring your SP to work with the IDP

First, navigate to http://simplesamlphp.test:8080/simplesaml/saml2/idp/metadata.php and set that metadata in your SP (how that's done may vary from SP to SP).

Next, add your SP's metadata to the container.  You can modify metadata/sam20-sp-remote.php directly with your SP metadata. It must be in the format described in the SimpleSAMLPHP [documentation](https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote). If you have an XML file that needs to be converted to the above format you can log into your SimpleSAMLPHP instance and use the [metadata converter tool](http://simplesamlphp.test:8080/simplesaml/admin/metadata-converter.php).

Alternately, you can copy your SP metadata XML file into the metadata folder and run the following command:
```console
docker run --rm -v /full/path/to/metadata:/var/simplesamlphp/metadata simplesamlphp:latest sh -c "php /var/simplesamlphp/bin/metadata-converter.php /var/simplesamlphp/metadata/myspmetadata.xml >> /var/simplesamlphp/metadata/saml20-sp-remote.php"
```

Two users with the following username/password are available to test:
- student/studentpass
- employee/employeepass

### Example SP configuration using Moodle

This example assumes you have a Moodle 3.3 installation in which you have administrator rights and that you have the [SAML2 plugin](https://github.com/catalyst/moodle-auth_saml2) installed and enabled in Moodle. For illustration purposes we'll assume that you access this site at http://moodle.test/. This example also assumes that you already have the SimpleSAMLPHP container running as noted above. Follow these steps to set up the SAML2 plugin as a service provider:

1. Navigate to http://simplesamlphp.test:8080/simplesaml/saml2/idp/metadata.php and copy the XML found there to your clipboard.
2. Navigate to http://moodle.test/admin/settings.php?section=authsettingsaml2 and paste the copied metadata into the "IdP metadata xml OR public xml URL" field. Set the following fields and save the settings:
    - "Display IdP link" to "Yes"
    - "Dual login" to "Yes"
    - "Auto create users" to "Yes"
    - "Mapping IdP" to "uid"
    - "Data mapping (Email address)" to "mail"
3. A certificate for the service provider should have been created by default. Check that certificate exists by clicking the "View SP certificate" link on the settings page. If it doesn't, create one by clicking the "Regenerate certificate" button from the settings page and filling in the fields appropriately.
4. From the SAML2 settings page, click the "View Service Provider Metadata" link and copy the XML found there to your clipboard.
5. Navigate to http://simplesamlphp.test:8080/simplesaml/, login as an administrator (admin/password), click on the "Federation" tab and then the "XML to SimpleSAMLphp metadata converter" link.
6. Paste the XML from step 4 into the "XML metadata" field and submit the form by clicking the "Parse" button.
7. Copy the code from saml20-sp-remote section and paste that into the metadata/saml20-sp-remote.php file that is part of this repo and save it.

You should now be able to log in using one of the two example users noted in the "Configuring your SP to work with the IDP" section above by opening a new session in your Moodle instance, clicking the "Login via SAML" link and entering the credentials. If all works well you should be redirected to your Moodle instance as an authenticated user.
