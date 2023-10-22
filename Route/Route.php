<?php

namespace Route;

use Exception;
use HtmlBuilder\HtmlDocumentController\DocumentEvaluation;
use Throwable;

require_once 'Response.php';
require_once 'Request.php';
class Route
{
    public string $request;
    public array | null $request_parameters;
    public int $response_code;
    public string $response_resource;

    public function __construct()
    {
        if (substr(php_sapi_name(), 0, 3) == 'cgi') {
            throw new Exception("cgi not supported in Route");
        }
        $this->request = str_replace('?' . $_SERVER['QUERY_STRING'], '', ltrim($_SERVER['REQUEST_URI'], '/'));
        $this->request_parameters = $_SERVER['QUERY_STRING'] == '' ? null : explode('&', $_SERVER['QUERY_STRING']);
        $this->type = in_array(explode('/', $this->request)[0], pariaform()->settings['active_backend_pages']) ? 'backend' : 'frontend';
        $this->response_resource = ($this->type == 'backend' ? 'src/backend/' : '') . 'content/' . $this->request;
        $this->response_code = file_exists($this->response_resource) ? 200 : 404;
    }




    public function serve()
    {
        switch ($this->type) {
            case 'frontend':

                // get page config
                $config_file = 'www-data/page-confs/' . ($this->request == '' ? 'main' : $this->request) . '-page_conf.ini';

                // point to requested page in pariaform-index
                pariaform()->requested_page = pariaform()->index->pages[trim('content/' . $this->request, '/')];
                if (file_exists($config_file)) {
                    $page_config = parse_ini_file($config_file, true, INI_SCANNER_TYPED);
                }

                if (pariaform()->get_system_setting('cache')) {
                    // display html if avaiable and stop code execution.
                    if ($page_config['allow_caching']) {
                        $html_file = 'www-data/cache/' . pariaform()->requested_page->name . '-cached.html';
                        if (file_exists($html_file)) {
                            include $html_file;
                            break;
                        }
                    }
                    // run pre boiled php if avaiable, buffer into pariaform, display html template and stop code execution.
                    $compiled_php_folder = 'www-data/cache/content/' . pariaform()->requested_page->name;
                    if (file_exists($compiled_php_folder)) {
                        pariaform()->page_content = pariaform()->consolidate_content_folder($compiled_php_folder . "/*-compiled.php");
                        require_html_template();
                        break;
                    }
                }
                // run code directly from content-folder
                $uncompiled_php_folder = $this->response_resource;
                if (file_exists($uncompiled_php_folder)) {
                    pariaform()->page_content = $this->consolidate_content_folder($uncompiled_php_folder . "*.{php,html}", true);
                    require_html_template();
                    break;
                }

            case 'backend':
                // point to requested page in pariaform-index
                // sitemap is not implemented for backend
                // pariaform()->requested_page = pariaform()->index->pages[trim('src/backend/content/' . $this->request, '/')];

                $backend_php_folder = 'src/backend/content/' . ($this->request == '' ? '' : $this->request . '/');
                pariaform()->page_content = $this->consolidate_content_folder($backend_php_folder . "*.{php,html}");
                require_backend_html_template();
        }
    }

    /**
     * Consolidates content of a given folder.
     *
     * @param  string $glob_pattern  the glob-pattern. Accepts {brackets}.
     * @param  bool   $uncompiled_code set true if the code needs to be compiled.
     * @return string html string.
     */
    private function consolidate_content_folder(string $glob_pattern, bool $uncompiled_code = false, bool $backend = false): string
    {
        ob_start();
        foreach (glob($glob_pattern, GLOB_BRACE) as $filename) {
            // if uncompiled code aka the 'content/'-folder, use DocumentEvaluation Class
            if ($uncompiled_code) new DocumentEvaluation($filename);
            // else it's html or normal php, just include
            else include $filename;
        }
        // store content in pariaform object.
        return ob_get_clean();
    }
}
