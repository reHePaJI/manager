<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Manager extends Controller {
  /**
   * OBJECT 
   * 
   * @const int
   */
  const OBJECT        = 1;
  /**
   * STRING 
   * 
   * @const int
   */
  const STRING        = 2;
  /**
   * @var string
   */
  protected $template = NULL;
  /**
   * @var View
   */
  protected $view = NULL;
  /**
   * 
   * @var string
   */
  protected $file = NULL;
  /**
   * @var boolean
   */
  protected $auto_render = TRUE;

  // private trigger_events() {{{ 
  /**
   * trigger_events
   * 
   * @access private
   * @return void
   */
  private function trigger_events(){
    $event = $this->request->param('_event');
    Manager::trigger( $event, $this->view, $this->request );
  }
  // }}}

  /**
   * @param boolean $exp
   * @param string $msg
   * @throws View_Exception
   */
  protected function ensure( $exp, $msg){
    if ( $exp)  throw new View_Exception( $msg);
  }
  /**
   */
  protected function initialize(){
  }

  /**
   */
  protected function finalize(){

  }
  /**
   * 
   * Enter description here ...
   * @param string $uri
   * @return string
   */
  protected function extend( $uri, $cache = TRUE, $check = self::STRING, array $gets = null ){
    if ( $cache) $cache = Manager::cache();
    else $cache = null;
    $oldgets   = null;
    if ( null !== $gets ) {
      $oldgets = Manager::gets( $gets );
      Manager::gets( $gets );
    }
    $response = Request::factory( $uri, $cache)->execute();
    if ( null !== $gets ) Manager::gets( $oldgets );
    if ( self::STRING === $check ) $response = (string)$response;
    if ( $this->view && strlen($this->file) )  $this->view->set_filename( $this->file);
    return $response;
  }

  // protected execute(uri,Viewview=NULL,cache=TRUE,check=self::STRING,arraygets=null) {{{ 
  /**
   * execute
   * 
   * @param string  $uri 
   * @param View    $view 
   * @param bool    $cache 
   * @param string  $check 
   * @param array   $gets 
   * @access protected
   * @return Response
   */
  protected function execute( $uri, View $view = NULL, $cache = TRUE, $check = self::STRING, array $gets = null ){
    if ( $cache) $cache = Manager::cache();
    else $cache = NULL;
    $response = Manager::execute( $uri, $view, $cache, $gets );
    if ( self::STRING === $check ) $response = (string)$response;
    if ( $this->view && strlen($this->file) )  $this->view->set_filename( $this->file);
    return $response;
  }
  // }}}
  
  /**
   * (non-PHPdoc)
   * @see Kohana_Controller::before()
   */
  public function before(){
    $this->initialize();
    if( $this->template != ''){
      $this->file = $this->template;
    }
    if ( $this->auto_render === TRUE){
      if ( $this->view = Manager::template()){
        if ( $this->file != NULL && strlen( $this->file ) )
          $this->view->set_filename( $this->file);
        else{
          $this->auto_render = FALSE;
          $this->file = $this->view->get_filename();
        }
      }
      elseif( $this->file != NULL && $this->file != ''){
        $this->view = View::factory( $this->file);
        Manager::template( $this->view );
      }
      else{
        $this->view = View_Null::factory();
        Manager::template( $this->view );
      }
    }
    $this->trigger_events();
    parent::before();
  }

  /**
   * Standart action method of controller from manager
   * For params of action
   */
  public function action_index(){
    $this->do_action();
  }

  /**
   * Innerhiting this method for your controller
   * For params of action
   */
  protected function do_action(){

  }

  /**
   * (non-PHPdoc)
   * @see Kohana_Controller::after()
   */
  public function after(){
    if ( $this->auto_render === TRUE && $this->view && !( $this->view instanceof View_Null ) ){
      $this->response->body( $this->view->render());
    }
    $this->finalize();
    parent::after();
  }

} // End Manager
