Mage Ape
========

### What?

Mage Ape is a web interface for testing API calls to Magento stores and sites

Magento is an extensable e-ecomerce framework with many moving parts. Just one such part is the SOAP or XML-RPC based API interface. Which allows 3rd party programs to access store content.

However, sometimes things fail. Mage Ape wants to help you troubleshoot.

<http://taoexmachina.com/mage-ape/>

### Dependencies

* PHP version 5.2 or greater
* php-soap module


### Recently implemented features

* Expanded and improved printed message based on returned HTTP codes for input URL. Mage-Ape will color the alert box for warning, sucess, or danger based on status code ranges 2xx, 3xx, 4xx, etc. It will also print the location specified in a redirect and the subsequent return code. 
* Mage-Ape now treats any path you add after the domain is where the WSLD will be located, and not change it. If you input a domain without path, Mage-Ape will still assume default magento API paths, and try to use those. Printed message will also reflect whether the default or specified URL is being used.
* Added buttons for magneto version and SOAP version 
* Added timestamps to printed messages

### Road Map / Ideas

* Keep a more detailed log of calls and responces, and print it out after all tests complete, or keep some local log file maybe.
* More optional switches for XML-RPC?
* Add Magento 2.x support
