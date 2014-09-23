# Yet Another Api Generator
- [What is That?](#what-is-that?)
- [How using it?](#how-using-it?)
- [How to extend Api?](#how-to-extend-api?)

## What is that?
This is a simple parsing tool wich automaticly parse somme file and if keywords are found, will generate the array for the rest api
The api can handle with the authenticate required function and with group privilege
## How using it?
### HTTP server: 
Activate the `url rewriting` and only call index.php.    
Like this, all call `http://yourhost.name/api/foo/bar` will always call `http://yourhost.name/api/index.php`
### Your index.php
```
	require('apimanager.php'); #include the file
	$api = new ApiManager();   #instance the class
	$api->setCache(true);	   #Indicate to the api to use caching system
	$api->load();              #parse all file and generate the api
	$return = $api->execute($elementsUrl,$auth,$group); #execute api
	echo($return) 			   #display the response of your api
```
### Param of $api->execute
`http://yourhost.name/api/foo/bar`
$elementsUrl must be = ['foo','bar'] (['modul','function','some param of the function if need'....])
$auth = (boolean) logged or not (default is false)
$group = (string) the group of the user (default is "user")
### Call
Simply call `http://yourhost.name/api/foo/bar`. If the is not into the api, the api will answerd you the possiblities.l
## How to extend Api?
The api is separated into module with cmd.

We will add the `foo` modul with `bar` as cmd for exemple

### Module
Write your class Foo or copy `apitest.php` file and change it (this file name must start with `api`)
Into the Class comment add `@apiname foo` like this:
```
	/**
	* My Foo Class
	* this class is simply a demo test for the api generator
	* @apiname test
	*/
```
### Function
#### Base
Into the Function comment add `@apifunction bar` like this: 
```
	/**
	* My bar Function
	* @param int $a ploup
	* @return string[] responce into an array
	* @apifunction bar
	*/
```
#### Param
All params can be present or not and can be mixed
`@apifunction bar default` function wich will be called if there is not cmd into the api call: `http://yourhost.name/api/foo/`
`@apifunction bar auth` require authentification for using this function
`@apifunction bar group:admin` require to be into admin group for used this function (multiples group can be set with a pipe `|` ex: `group:admin|user`)
`@apifunction bar cache` generate cache file for this function (this cache will be used if cache is activate)

If you don't intent to use group or identification into your api, just don't set it into the options

### Cache Delete file
Into the Function comment add `@apicachedel foo bar` like this: 
```
	/**
	* My bar Function
	* @param int $a ploup
	* @return string[] responce into an array
	* @apicachedel foo bar
	*/
```

This will indicate at the api to delete file cache for the class function (if caching is used, the file will be generate the next call to the call)  
This is usefull when inserting element into database.  
If this is used for class name, this indicate that the function to delete uis in the same class than the func

### Return details
Into the Function comment add `@apiresponce bar` like this: 
```
	/**
	* My bar Function
	* @param int $a ploup
	* @return string[] responce into an array
	* @apiresponce blabla for responce 
	*/
```
In the help of the api this will add the return element details

That All Folks; Now you can call `http://yourhost.name/foo/bar/` and you will have the return of the bar function