<?php

namespace App\Mail;

/**
 * The Email class represents a single email
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Email
{
    /**
     * @var string
     */
    protected $namespace = 'App\Mail';

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $fromName = null;

    /**
     * @array
     */
    protected $to = [];

    /**
     * @var array
     */
    protected $cc = [];

    /**
     * @var array
     */
    protected $bcc = [];

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $templateHtml = null;

    /**
     * @var string
     */
    protected $templateText = null;

    /**
     * @var array
     */
    protected $templateContext = [];

    /**
     * Get the from address
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set the from address
     *
     * @param string $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setFrom($value)
    {
        $this->from = $value;

        return $this;
    }

    /**
     * Get the from name
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set the from name
     *
     * @param string $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setFromName($value)
    {
        $this->fromName = $value;

        return $this;
    }

    /**
     * Get the 'to' addresses
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set a 'to' address
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function addTo($email, $name = null)
    {
        $this->to[$email] = $name;
        return $this;
    }

    /**
     * Get the 'cc' addresses
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set a 'cc' address
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function addCc($email, $name = null)
    {
        $this->cc[$email] = $name;

        return $this;
    }

    /**
     * Get the 'bcc' addresses
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Set a 'cc' address
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function addBcc($email, $name = null)
    {
        $this->bcc[$email] = $name;

        return $this;
    }

    /**
     * Get the subject line
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the subject for this email
     *
     * @param string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the HTML template filename for this email
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getTemplateHtml()
    {
        if (is_null($this->templateHtml)) {
            $this->templateHtml = $this->getDefaultTemplateName() . '.html.twig';
        }

        return $this->templateHtml;
    }

    /**
     * Get the plain text  template filename for this email
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getTemplateText()
    {
        if (is_null($this->templateText)) {
            $this->templateText = $this->getDefaultTemplateName() . '.txt.twig';
        }

        return $this->templateText;
    }

    /**
     * Get the template context
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getTemplateContext()
    {
        return $this->templateContext;
    }

    /**
     * Set the template context
     *
     * @param  array $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setTemplateContext($value)
    {
        $this->templateContext = $value;

        return $this;
    }

    /**
     * Add a key to the template context
     *
     * @param string $key
     * @param mixed $value
     * @return static
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function addTemplateContext($key, $value)
    {
        $this->templateContext[$key] = $value;

        return $this;
    }

    /**
     * Get the default template name
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getDefaultTemplateName()
    {
        $name = str_replace(
            [$this->namespace . '\\'],
            [''],
            get_called_class()
        );
        if ('Mail' == substr($name, -4)) {
            $name = substr(
                $name,
                0,
                -4
            );
        }
        $name = explode(
            '\\',
            $name
        );
        array_walk(
            $name,
            function (&$val) {
                $val = lcfirst($val);
            }
        );
        $name = '@emails/' . implode('/', $name);

        return $name;
    }
}
