<?php
namespace PhpSkeleton;

class SkeletonResponse {
	/**
	 * Object to be returned by a request handler.
	 * @param SkeletonRequest $request	the request that triggered this respons
	 * @param mixed $data			the data to be returned (more on this below)
	 * @param string $view			the view name to use for rendering
	 * @param boolean $inherit_template	if we are using a view, should it inherit from the template?
	 *
	 * If $view is not set, then we just output $data as a
	 * string. This means that if you want to output something
	 * like JSON, you can just leave out $view, and you'll
	 * basically just get $data echo-ed out. If you do set view,
	 * then we look for an html file in the configured views
	 * directory (defaults to "views" in the webroot) and displays
	 * that within the template.html view.
	 *
	 * The template.html view is used as a wrapper for all other
	 * views. We don't have anything fun like template inheritance
	 * yet, but template.html should contain the line:
	 * <?php include $SKELETON_VIEW; ?>
	 * which will output the inner view.
	 */
	public function __construct($request, $data, $view=NULL, $inherit_template=TRUE) {
		$this->_data = $data;
		$this->_request = $request;
		$this->_headers = array();
		$this->_inherit_template = $inherit_template;

		if($view !== NULL) {
			// For safety, if there are any dots in the
			// view, or it starts with a slash, ignore
			// it. Hopefully this value isn't injectable
			// at all, but safety first.
			if((strpos('.', $view) === FALSE) and (strpos('/', $view) !== 0)) {
				$VIEW_DIR = $this->_request->_view_dir();
				$this->_view = $VIEW_DIR.'/'.$view.'.html';
			} else {
				throw new \Exception("Invalid view path: '".$view."'");
			}

		} else {
			$this->_view = NULL;
		}
	}

	public function __toString() {

		if($this->_view === NULL) {
			$this->_headers[] = "Content-Type:text/plain";
			return (string) $this->_data;
		} else {

			$this->_headers[] = 'HTTP/1.1 200 OK';

			// I guess this would break things.
			// (That works on two levels)
			unset($this->_data['this']);
			// dirty but it works
			extract($this->_data);

			if(!isset($title) and isset($this->_request->_config['TITLE'])) {
				$title = $this->_request->_config['TITLE'];
			}

			if($this->_inherit_template) {
				$SKELETON_VIEW = $this->_view;
				$VIEW_DIR = $this->_request->_view_dir();

				ob_start();
				include $VIEW_DIR.'/template.html';
				$out = ob_get_contents();
				ob_end_clean();
			} else {
				ob_start();
				include $this->_view;
				$out = ob_get_contents();
				ob_end_clean();
			}

			return $out;
		}
	}

	public function headers() {
		return $this->_headers;
	}

	public function add_header($header) {
		$this->_headers[] = $header;
	}

	/**
	 * A 404 response. Will render using a view called 404.html
	 * within the views directory and will pass $message out to
	 * that view.
	 */
	public static function error404($request, $message) {
		$req = new static($request, array('message' => $message), '404');
		$req->add_header('HTTP/1.1 404 Not Found');
		return $req;
	}
}
