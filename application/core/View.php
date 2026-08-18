<?php
class View
{
	function generate($content_view, $template_view, $data = null)
	{
		if (is_array($data)) {
			extract($data); // превращает ['error' => '...'] в $error
		}
		include $_SERVER['DOCUMENT_ROOT'] . '/application/views/' . $template_view;
	}
}