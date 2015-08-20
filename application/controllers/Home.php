<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('security');
        $this->load->library('form_validation');
        $this->load->helper('form');
    }

    public function index()
    {
        $this->form_validation->set_rules('dummyEmail','email address','trim|required|valid_email|xss_clean');

        $this->form_validation->set_message('required', 'You must enter a %s.');
        $this->form_validation->set_message('valid_email', "Please double check the %s you entered.");

        if ($this->form_validation->run() == FALSE)
        {
            // Default Views
            $this->load->view('home_index');
        }
        else
        {
            // Validated Successfully
            $this->_verifySignup($this->input->post('dummyEmail'));
        }
    }

    private function _verifySignup($dummyEmail)
    {
        // Hardcoded sign-up page to search
        $url = 'http://underconstructiontemplate.com/';
        $this->load->model('home_verify');

        // Prevent request flooding by checking if URL was previously verified
        if($this->home_verify->status($url)=="on-file"){
            $data['result'] = "Entry Already On-File";
            $this->load->view('home_index',$data);
        }
        else
        {
            // Load DOM
            $doc = new DOMDocument();
            @$doc->loadHTMLFile($url);
            $xpath = new DOMXpath($doc);

            // Get DOMNodeList of <input> fields within each form
            $get_FormInputs = $xpath->query('//form/input');

            // Turn the <input> DOMNodeList into a workable array
            $FormInputs = array();
            foreach($get_FormInputs as $FormInput)
            {
                $FormInputs[] = array(
                    'id'          => $FormInput->getAttribute('id'),
                    'name'        => $FormInput->getAttribute('name'),
                    'value'       => $FormInput->getAttribute('value'),
                    'max-length'  => ($FormInput->getAttribute('max-length')?$FormInput->getAttribute('max-length'):'false'),
                    'type'        => $FormInput->getAttribute('type'),
                    'is_verified' => 'false'
                );
            }

            /*
             * Custom 'strpos' or string position implementation.
             * Allows the program to search through an array of needles
             * to find first <input> value condition that indicates an e-mail requirement
             */
            function strpos_array($haystack, $needle)
            {
                // Check and set the $needle to an array if need be.
                if(!is_array($needle))
                {
                    $needle = array($needle);
                }
                foreach($needle as $query)
                {
                    if($position = strpos($haystack, $query) != false)
                    {
                        return true;
                    }
                }
                return false;
            }

            // Array of <input> value conditions
            $InputConditions = array('@','at','mail','e-mail');

            // Verify whether or not an input is for an e-mail
            $i = 0;
            while ($i<count($FormInputs))
            {
                if($FormInputs[$i]['type']=='email')
                {
                    $FormInputs[$i]['is_verified'] = 'true';
                }
                if($FormInputs[$i]['max-length']=='254')
                {
                    $FormInputs[$i]['is_verified'] = 'true';
                }
                if($FormInputs[$i]['type']=='text')
                {
                    if(strpos_array($FormInputs[$i]['value'],$InputConditions)==true){
                        $FormInputs[$i]['is_verified'] = 'true';
                        $i++;
                    } else {
                        $FormInputs[$i]['is_verified'] = 'false';
                        $i++;
                    }
                }
                $i++;
            }

            /**
             *  Create a Query for the E-Mail Sign-up Form
             *
             *  Note: This could be expanded to account for all 24 input element types,
             *        but to keep things easy I stopped at what the specified page required.
             */
            $form_fields = array();
            for($i2=0;$i2<count($FormInputs);$i2++)
            {
                if($FormInputs[$i2]['is_verified']=='true')
                {
                    $form_fields += array($FormInputs[$i2]['name']=>$dummyEmail);
                }
            }

            // Get <form> action path
            $get_FormAction = $xpath->query('//form/@action');

            // Open cURL connection
            $FormAction = $get_FormAction->item(0)->nodeValue.'/';

            $fields = " ";
            // Make the data URL compliant for POST
            foreach($form_fields as $key=>$value)
            {
                $fields .= $key.'='.$value.'&';
            }
            rtrim($fields,'&');

            // Open cURL connection
            $curl = curl_init();
            // Set URL, POST vars, and data
            curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt ($curl, CURLOPT_HEADER, false);
            curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt ($curl, CURLOPT_URL, $FormAction);
            curl_setopt ($curl, CURLOPT_POST, count($form_fields));
            curl_setopt ($curl, CURLOPT_POSTFIELDS, $fields);
            curl_setopt ($curl, CURLOPT_REFERER, $FormAction);
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt ($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
            // Request POST result page
            $curl_result = curl_exec ($curl);
            // Close cURL connection
            curl_close ($curl);

            // Load DOM for POST results
            $output = new DOMDocument();
            libxml_use_internal_errors(true);
            $output->loadHTML($curl_result);
            $xpath2 = new DOMXpath($output);

            // Check for confirmation validation error/message container's class
            $get_UserEmailErrors = $xpath2->query('//*[@class="userEmailError"]');

            // Turn 'userEmailError' DOMNodeList into usable array
            $userEmailErrors = array();
            foreach($get_UserEmailErrors as $EmailErrors)
            {
                $userEmailErrors[] = array(
                    'value' => $EmailErrors->nodeValue
                );
            }

            // Verify presence of sign-up success confirmation
            if(isset($userEmailErrors[0])==true && $userEmailErrors[0]['value']=="Your download link has been sent!")
            {
                // Success
                $is_verified = true;
                $this->home_verify->verify($url, $is_verified);
            } else
            {
                // Failure
                $is_verified = false;
                $this->home_verify->verify($url, $is_verified);
            }
        }
    }
}