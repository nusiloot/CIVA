<?php
/**
 * acExceptionNotifier allows you to handle the application exception
 *
 * @package    acExceptionNotifier
 * @subpackage lib
 * @author     Jean-Baptiste Le Metayer <lemetayer.jb@gmail.com>
 * @author     Vincent Laurent <vince.laurent@gmail.com>
 * @version    0.1
 */
class acExceptionEmailNotifier implements INotifier
{

  /**
   * Handles the notification for the given event 
   *
   * @param sfEvent $event
   * @access public
   * @static
   */
	public static function exceptionHandler(sfEvent $event)
	{	
		if (!sfConfig::get('sf_debug') && is_object($exception = $event->getSubject())) {
			$acException = new acException($exception, sfConfig::get('app_ac_exception_notifier_format'));
			$traces = self::renderTraces($acException);
			self::notify($traces);
		}
	}

  /**
   * Renders exception traces
   *
   * @param acException $acException
   * @access private
   * @static
   * @return string The exception traces
   */
	private static function renderTraces(acException $acException)
	{
		$traces  = implode('<br />', $acException->getExceptionInformations());
		$traces .= implode('<br />', $acException->getExceptionTraces());
		$traces .= '<hr />';
		$traces .= implode('<br />', $acException->getDebugTraces());
		return $traces;
	}

  /**
   * Notify by e-mail the given message
   *
   * @param string $message         A message to notify
   * @access private
   * @static
   */
	private static function notify($message)
	{
		acEmailNotifier::exceptionEmailNotifier($message);
	}
}