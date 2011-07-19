#NineThousandCompreduxBundle#

##Overview##
This bundle lets you configure and dynamically create ninethousand-compredux instances as services. From the configuration of your application you can easily create services as new instances of compredux with a few simple configurations.

This bundle uses your Symfony2 app/cache folder to serve the compredux cache

##Ninethousand Compredux##

Compredux is a php content filtering proxy. With compredux you can deliver web content by proxy and arrange and filter the content with CSS selectors. compredux caches external web resources in it's own caching folder.

##Configuration##

Add the compredux dependency in your deps file:

    [NineThousandJobqueueBundle]
        git=http://github.com/ninethousand/NineThousandCompreduxBundle.git
        target=/bundles/NineThousand/Bundle/NineThousandCompreduxBundle
        
install it from the root of your symfony project with:

    user@server:~/mySymfonyProject$ bin/vendors install

Add the following to your autoload.php file:

    $loader->registerNamespaces(array(
        //...
        'NineThousand'     => __DIR__.'/../vendor/bundle',
    ));
    
Add The NineThousandCompreduxBundle bundle to your kernel bootstrap sequence

    public function registerBundles()
    {
        $bundles = array(
            //...
            new NineThousand\Bundle\NineThousandCompreduxBundle\NineThousandCompreduxBundle(),
        );
        //...

        return $bundles;
    }

Add the service configuration to app/config.yml

    nine_thousand_compredux: 
        proxies:
            myproxy:  #name of the proxy (your service will be named compredux.myproxy for example)
                controller : project/compredux/
                server : https://github.com 
                curl_options: #curl options
                    CURLOPT_CONNECTTIMEOUT : 20
                    CURLOPT_HEADER         : true
                    CURLOPT_FAILONERROR    : true
                    CURLOPT_FILETIME       : true
                    CURLOPT_FOLLOWLOCATION : true
                    CURLOPT_RETURNTRANSFER : true
                    CURLOPT_SSL_VERIFYHOST : false
                    CURLOPT_SSL_VERIFYPEER : false
                    
## Usage instructions ##

### Routing ###
Set up the routing any way you normally do
    <?xml version="1.0" encoding="UTF-8" ?>

    <routes xmlns="http://symfony.com/schema/routing"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

        ...
        <route id="_project_compredux_" pattern="/project/compredux/">
            <default key="_controller">::ProjectController:compredux</default>
        </route>
        
        ...
    </routes>

### Controller ###
The Compredux bundle will create services for all of your proxies so all you need to do is access them in your controller, make the request and query the content like so:

    <?php

    namespace MyProject\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Symfony\Component\HttpKernel\Exception\HttpException;


    class ProjectController extends Controller
    {
        
        public function compreduxAction()
        { 
            
            $compredux = array();
            $client = $this->container->get('compredux.myproxy');
            $client->request();
            if ($client->hasErrors() && ($error = $client->getErrors())) {
               if (false !== strpos($error, '404')) {
                   throw new NotFoundHttpException('Compredux request returned 404');
               } else {
                   throw new HttpException('Compredux request returned error');
               }
            }
            $client->initHeaders();

            if (!$client->isType('html') && !$client->hasErrors()) {
                echo $client->getContent();
                exit();
            }
            
            $compredux['headscript'] = $client->getContent('head script');
            
            $compredux['bodyscript'] = $client->getContent('body script');
            $compredux['headlink'] = $client->getContent('head link');
            
            /**
             * Get the content of the request
             * @param  string null|string|array $include all of the CSS selectors which will be included 
             * @param  string null|string|array $exclude all of the CSS selectors which will *NOT* be included 
             * @return string
             */
            $compredux['content'] =  $client->getContent(
                array(
                    '#slider',
                    '#guides',
                    '#toc', 
                    '#files', 
                    '#issues_next',
                ), 
                array(
                    '.big-actions',
            ));
            
            return $this->render('MyProject:Project:index.html.twig', array('compredux' => $compredux));
        }
    }

### Templating ###
Then in the template you can use your filtered content like this:

     {% extends 'MyProject::layout.html.twig' %}
     {% block title "compredux | myproject.com" %}
     {% block body %} 
        {% autoescape false %}{{ compredux.headlink }}{{ compredux.headscript }}{{ compredux.bodyscript }}{% endautoescape %}
        <div id="project" >
            {% autoescape false %} {{ compredux.content }} {% endautoescape %}
        </div>
     {% endblock %}

