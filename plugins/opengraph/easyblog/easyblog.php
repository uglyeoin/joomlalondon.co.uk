<?php
/**
 * @package         JFBConnect
 * @copyright (c)   2009-2017 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @build-date      2017/03/29
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('sourcecoast.articleContent');
jimport('sourcecoast.openGraphPlugin');

define('EB_POST_BLANK'      , 9);
define('EB_POST_DRAFT'      , 3);
define('EB_POST_PENDING'    , 4);
define('EB_POST_PUBLISHED'  , 1);
define('EB_POST_SCHEDULED'  , 2);
define('EB_POST_UNPUBLISHED', 0);
define('EB_POST_NORMAL', 0);
define('EB_POST_TRASHED', 1);
define('EB_POST_ARCHIVED', 2);

class plgOpenGraphEasyBlog extends OpenGraphPlugin
{
    protected function init()
    {
        $this->extensionName = "EasyBlog";
        $this->supportedComponents[] = 'com_easyblog';
        $this->supportedAutopostLabel = "Post";
        $this->supportedAutopostTypes[] = 'post';
        $this->setsDefaultTags = true;

        // Define all types of pages this component can create and would be 'objects'
        $this->addSupportedObject("Blog Post", "post");
//        $this->addSupportedObject("Category", "category");

        // Add actions that aren't passive (commenting, voting, etc).
        // Things that trigger just by loading the page should not be defined here unless extra logic is required
        // ie. Don't define reading an article
        $this->addSupportedAction("Rate", "rate");
        $this->addSupportedAction("Comment", "comment");
    }

    protected function findObjectType($queryVars)
    {
        // Setup Object type for page
        $view = array_key_exists('view', $queryVars) ? $queryVars['view'] : '';

        // To determine if we are viewing normal entry layout or preview layout
        $layout = array_key_exists('layout', $queryVars) ? $queryVars['layout'] : '';
        $object = null;

        if ($view == 'entry')
        {
            $this->loadEB();
            $objectTypes = $this->getObjects('post');

            // If layout is preview, we need to get the proper blog id.
            // For preview layout, the id will be uid.
            $blogId = $layout && $layout == 'preview' ? $queryVars['uid'] : $queryVars['id'];

            //$post = EB::post($blogId); /* Do not use EB::post, not available in older versions EB */
            $post = $this->getEBTable('Blog');
            $post->load($blogId);

            if ($post)
            {
                $catId = $post->category_id;
                $object = $this->getBestCategory($objectTypes, $catId);
            }
        }

        return $object;
    }

    private function getBestCategory($objectTypes, $catId)
    {
        $object = null;
        if ($objectTypes)
        {
            $bestDistance = 99999;
            $this->db->setQuery("SELECT lft, rgt FROM #__easyblog_category WHERE id = " . $catId);
            $catLoc = $this->db->loadObject();
            foreach ($objectTypes as $type)
            {
                if($bestDistance == 99999 && $type->params->get('category') == 0)
                {
                    $object = $type;
                }
                else
                {
                    $this->db->setQuery("SELECT lft, rgt FROM #__easyblog_category WHERE id = " . $type->params->get('category'));
                    $result = $this->db->loadObject();
                    if ($result->lft <= $catLoc->lft && $result->rgt >= $catLoc->rgt)
                    {
                        $distance = $result->rgt - $result->lft;
                        if ($distance < $bestDistance)
                            $object = $type;
                        if ($distance == 1)
                            break;
                    }
                }
            }
        }
        return $object;
    }

    protected function setOpenGraphTags()
    {
        $desc = ''; //Note: meta is same as blank value, since system plugin attempts to generate from metadescription if no value is found
        $image = '';

        $view = JRequest::getCmd('view');

        if($this->object)
        {
            $desc_type = $this->object->params->get('custom_desc_type');
            $desc_length = $this->object->params->get('custom_desc_length');
            $image_type = $this->object->params->get('custom_image_type');
            $image_path = $this->object->params->get('custom_image_path');
            $title_type = $this->object->params->get('custom_title_type');
        }
        else
        {
            $desc_type = 'custom_desc_introwords';
            $desc_length = '50';
            $image_type = 'custom_image_item';
            $image_path = '';
            $title_type = 'custom_title_entry';
        }

        if ($view == 'entry')
        {
            $oldEasyBlogVersion = $this->loadEB();
            $item = $this->getEBTable('Blog');
            $item->load(JRequest::getInt('id'));

            if($title_type == 'custom_title_entry')
            {
                $title = $item->title;
            }
            else //if($title_type == 'custom_title_page')
            {
                $document = JFactory::getDocument();
                $title = $document->getTitle();
            }
            $this->addOpenGraphTag('title', $title, false);

            $itemText = trim(strip_tags($item->intro)) . ' ' . trim(strip_tags($item->content));
            if ($desc_type == 'custom_desc_introwords')
                $desc = $this->getSelectedText($itemText, SC_INTRO_WORDS, $desc_length);
            else if ($desc_type == 'custom_desc_introchars')
                $desc = $this->getSelectedText($itemText, SC_INTRO_CHARS, $desc_length);
            $this->addOpenGraphTag('description', $desc, false);

            //if ($image_type == 'custom_image_item')
            //{
            $image = $this->getEasyBlogMainImage($item, $oldEasyBlogVersion);
            //}
            if ($image_type == 'custom_image_first' || $image == '')
            {
                $tmpImage = $this->getFirstImageFromText($item->intro);
                if($tmpImage != '')
                    $image = $tmpImage;
                else
                {
                    $tmpImage = $this->getFirstImageFromText($item->content);
                    if($tmpImage != '')
                        $image = $tmpImage;
                }
            }
            if ($image_type == 'custom_image_category' || $image == '')
            {
                $tmpImage = $this->getEasyBlogCategoryImage($item->category_id);
                if($tmpImage != '')
                    $image = $tmpImage;
            }
            if (($image_type == 'custom_image_custom' || $image == '') && $image_path != '')
            {
                $image = $image_path;
            }
            $this->addOpenGraphTag('image', $image, false);

            /*// Item Author
            if(isset($item->created_by))
            {
                $this->db->setQuery("SELECT name FROM #__users WHERE id=".$item->created_by);
                $author = $this->db->loadResult();
                $this->addOpenGraphTag('author', $author, false);
            }*/
        }
    }

    /************* DEFINED ACTIONS CALLS *******************/
    protected function checkActionAfterRoute($action)
    {
        /***************** NEW VOTE ******************/

        if ((JRequest::getCmd('format') == 'ejax' && JRequest::getCmd('layout') == 'vote' && $action->system_name == 'rate') ||
            (JRequest::getCmd('format') == 'ajax' && JRequest::getCmd('namespace') == 'siteviewsratingsvote'  && $action->system_name == 'rate'))
        {
            $my = JFactory::getUser();

            $oldEasyBlogVersion = $this->loadEB();
            $config = $this->getEBConfig();
            $blog = $this->getEBTable('Blog');
            $rating = $this->getEBTable('Ratings');

            if($oldEasyBlogVersion)
            {
                $value = JRequest::getInt('value0'); // Rating
                $uid = JRequest::getInt('value1'); // Blog ID
                $type = JRequest::getCmd('value2'); // should be 'entry'
            }
            else
            {
                $value = JRequest::getInt('value'); // Rating
                $uid = JRequest::getInt('id'); // Blog ID
                $type = JRequest::getCmd('type'); // should be 'entry'
            }

            $blog->load($uid);

            if ($config->get('main_password_protect', true) && !empty($blog->blogpassword))
            {
                return;
            }

            if($oldEasyBlogVersion)
            {
                // Do not allow guest to vote, or if the voter already voted.
                if ($rating->fill($my->id, $uid, $type, JFactory::getSession()->getId()) || ($my->id < 1 && !$config->get('main_ratings_guests')))
                    return;
            }
            else
            {
                // Get the user's session
                $session = JFactory::getSession();

                // Do not allow guest to vote, or if the voter already voted.
                $exists = $rating->load(array('created_by' => $my->id, 'uid' => $uid, 'type' => $type, 'sessionid' => $session->getId()));

                if ($exists || ($my->guest && !$this->config->get('main_ratings_guests'))) {
                    return;
                }
            }

            $uri = JURI::getInstance();
            $url = $uri->toString(array('scheme', 'host', 'port'));
            $url = $url . JRoute::_('index.php?option=com_easyblog&view=entry&id=' . $uid, false);
            $this->triggerAction($action, $url);
        }

        /*************** NEW COMMENT ******************/
        if ((JRequest::getCmd('format') == 'ejax' && JRequest::getCmd('layout') == 'commentSave' && $action->system_name == 'comment') ||
            (JRequest::getCmd('format') == 'ajax' && JRequest::getCmd('namespace') == 'siteviewscommentssave'  && $action->system_name == 'comment'))
        {
            $oldEasyBlogVersion = $this->loadEB();
            $config = $this->getEBConfig();

            // Get the post data
            // From /components/com_easyblog/controller.php
            $data = JRequest::get('POST', JREQUEST_ALLOWHTML);
            $post = array();

            foreach ($data as $key => $value)
            {
                if ((JString::substr($key, 0, 5) == 'value' && $oldEasyBlogVersion) ||
                    ($key=='comment' && !$oldEasyBlogVersion))
                {
                    if (is_array($value))
                    {
                        $arrVal = array();
                        foreach ($value as $val)
                        {
                            $item = $val;
                            $item = stripslashes($item);
                            // $item   = rawurldecode($item);
                            $arrVal[] = $item;
                        }
                        $arrVal = EasyBlogStringHelper::ejaxPostToArray($arrVal);
                        $post = $arrVal;
                    } else
                    {
                        $val = stripslashes($value);
                        $val = rawurldecode($val);
                        $post['message'] = $val;
                    }
                }
            }

            $acl = $this->getEBRuleSet();

            if (empty($acl->rules->allow_comment))
                return;

            if (!$config->get('comment_require_email') && !isset($post['esemail']))
            {
                $post['esemail'] = '';
            }

            if($oldEasyBlogVersion)
            {
                // Load the EasyBlog view class to run their own validation tests
                require_once(JPATH_SITE.'/components/com_easyblog/views.php');
                require_once(JPATH_SITE.'/components/com_easyblog/views/entry/view.ejax.php');

                // Weird hack to prevent a PHP warning which prevents the AJAX completion in Joomla 2.5
                if (!defined('JPATH_COMPONENT'))
                    define("JPATH_COMPONENT", JPATH_BASE . '/components/com_easyblog');

                // @task: Run some validation tests on the posted values.
                $ebView = new EasyBlogViewEntry();
                if (!$ebView->_validateFields($post))
                    return;

                $blogId = $post['id'];
            }
            else
            {
                $comment = EB::table('Comment');
                $blogId = $data['blogId'];

                $email = '';
                $name = '';
                $username = '';
                $commentData = array('post_id' => $data['blogId'], 'comment' => $data['comment'], 'title' => $data['title'], 'email' => $email, 'name' => $name, 'username' => $username, 'terms' => $data['terms']);
                $state = $comment->validatePost($commentData);
                if (!$state)
                    return;
            }

            $uri = JURI::getInstance();
            $url = $uri->toString(array('scheme', 'host', 'port'));
            $url = $url . JRoute::_('index.php?option=com_easyblog&view=entry&id=' . $blogId, false);
            $this->triggerAction($action, $url);
        }
    }

    /* Images and Descriptions */
    protected function getEasyBlogMainImage($article, $oldEasyBlogVersion)
    {
        $url = '';

        if($oldEasyBlogVersion)
        {
            if (isset($article->image))
            {
                $image = json_decode($article->image);
                if (isset($image->url))
                {
                    $filePath = str_replace(JURI::root(), '', $image->url);

                    jimport('joomla.filesystem.file');
                    if (JFile::exists($filePath))
                        $url = $image->url;
                }
            }
        }
        else
        {
            $post = EB::post($article->id);
            $juri = JURI::getInstance();
            $scheme = $juri->getScheme();
            $image = $post->getImage('original');

            if($image && strpos($image, 'placeholder-image.png') === false)
            {
                //check if we add the scheme since the default image already have the scheme added
                if(strpos($image, $scheme) !== false)
                    $url = $image;
                else
                    $url = $scheme . ':'. $image;
            }
        }

        return $url;
    }

    protected function getEasyBlogCategoryImage($catid)
    {
        $url = '';
        $category = $this->getEasyBlogCategory($catid);

        if (isset($category->avatar))
        {
            $image = $category->avatar;
            $imageName = 'images/easyblog_cavatar/' . $image;

            jimport('joomla.filesystem.file');
            if (JFile::exists($imageName))
                $url = JURI::base() . $imageName;
        }
        return $url;
    }

    protected function getCurrentEasyBlogCategoryId()
    {
        $catid = JRequest::getInt('id');
        return $catid;
    }

    protected function getEasyBlogCategory($catid)
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_easyblog/tables');
        $category = JTable::getInstance('Category', 'EasyBlogTable');
        if ($category) // Some users have reported this not coming back. Haven't determined why, but this check should fix.
            $category->load($catid);
        return $category;
    }

    protected function getBestText($item)
    {
        $itemText = trim(strip_tags($item->intro)) . ' ' . trim(strip_tags($item->content));
        return $this->getSelectedText($itemText, SC_INTRO_WORDS, 20);
    }

    /* Determine version of EasyBlog */
    protected function loadEB()
    {
        if(JFile::exists(JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php'))
        {
            require_once(JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php');
            return false;
        }
        else
        {
            require_once(JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
            return true;
        }
    }

    protected function getEBTable($tableName)
    {
        if(JFile::exists(JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php'))
            $rating = EB::table($tableName);
        else
            $rating = EasyBlogHelper::getTable($tableName, 'Table');
        return $rating;
    }

    protected function getEBConfig()
    {
        if(JFile::exists(JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php'))
            $config = EB::table('Configs');
        else
            $config = EasyBlogHelper::getConfig();
        return $config;
    }

    protected function getEBRuleSet()
    {
        if(JFile::exists(JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php'))
            $acl = EB::acl();
        else
            $acl = EasyBlogACLHelper::getRuleSet();

        return $acl;
    }

    /* Auto post */
    static $autopostArticle = null;
    public function onContentAfterSave($context, $article, $isNew)
    {
        if($context == 'easyblog.blog' && get_class($article) == 'EasyBlogPost')
        {
            $this->tryAutoPublish($article, $article->published);
        }
    }

    public function getAutopostMessage($ogObject, $id)
    {
        $message = $this->getBestText(self::$autopostArticle);
        return $message;
    }

    private function tryAutoPublish($article, $value)
    {
        self::$autopostArticle = $article;

        // Get object that $articleId belongs to
        $objectTypes = $this->getObjects('post');
        $ogObjects = $this->getObjectsForArticle($objectTypes, $article->category_id);

        if($article->state == EB_POST_NORMAL)
        {
            $isPending = $this->isArticlePublishPending($article->publish_up) || ($article->published == EB_POST_PENDING);

            if($value == EB_POST_PUBLISHED || $value == EB_POST_PENDING || $value == EB_POST_SCHEDULED)
            {
                $link = 'index.php?option=com_easyblog&view=entry&id='.$article->id;
                $this->autopublish($ogObjects, $article->id, $link, $isPending);
            }
            else if($isPending && $value == EB_POST_UNPUBLISHED)
            {
                $this->removePublish($ogObjects, $article->id);
            }
        }
        else
        {
            $this->removePublish($ogObjects, $article->id);
        }

        self::$autopostArticle = null;
    }

    public function openGraphUpdatePending($articleId, $link, $ext)
    {
        if($ext == $this->supportedComponents[0])
        {
            $this->loadEB();
            $blog = $this->getEBTable('Blog');
            $blog->load($articleId);
            self::$autopostArticle = $blog;

            $isPending = $this->isArticlePublishPending($blog->publish_up) || ($blog->published == EB_POST_PENDING);
            if(!$isPending)
            {
                // Get object that $articleId belongs to
                $objectTypes = $this->getObjects('post');
                $ogObjects = $this->getObjectsForArticle($objectTypes, $blog->category_id);

                $this->updatePending($ogObjects, $articleId, $link);
            }

            self::$autopostArticle = null;
        }
    }

    private function getObjectsForArticle($objectTypes, $articleCatId)
    {
        $validObjects = array();
        if ($objectTypes)
        {
            foreach ($objectTypes as $type)
            {
                if($type->params->get('category') == 0 || $type->params->get('category') == $articleCatId)
                {
                    $validObjects[] = $type;
                }
                else //is parent category
                {
                    $this->db->setQuery("SELECT lft, rgt FROM #__easyblog_category WHERE id = " . $type->params->get('category'));
                    $result = $this->db->loadObject();

                    if ($result->lft <= $articleCatId && $result->rgt >= $articleCatId)
                    {
                        $validObjects[] = $type;
                    }
                }
            }
        }
        return $validObjects;
    }

}