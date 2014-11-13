<?php
namespace Core;
/**
 * OutputWriter
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
abstract class OutputWriter {

    /**
     * @var InputReader The input reader of the current request
     */
    protected $_inputReader;

    /**
     * @var mixed The response to be written
     */
    protected $_response;

    /**
     * Creates an output writer
     *
     * @param InputReader $inputReader
     */
    protected function __construct(InputReader $inputReader = null) {
        $this->_inputReader = $inputReader;
    }

    /**
     * Creates an OutputWriter
     *
     * @static
     * @param string $writer The name of the writer
     * @param InputReader $inputReader The reader of the current request
     * @return OutputWriter
     */
    public static function createOutputWriter($writer, InputReader $inputReader = null) {
        $writerClassName = '\\Core\\OutputWriter\\' . $writer;

        return new $writerClassName($inputReader);
    }

    /**
     * Sets and writes the response
     *
     * @param mixed $response The response to be written
     */
    public function setResponse($response) {
        $this->_response = $response;
        $this->_writeResponse();
    }

    /**
     * Sets and writes an exception
     *
     * @param \Exception $exception
     */
    public function setException($exception) {
        $this->_response = $exception;
        $this->_writeException();
    }

    /**
     * Writes the response
     *
     * @abstract
     */
    abstract protected function _writeResponse();

    /**
     * Writes an exception
     *
     * @abstract
     */
    abstract protected function _writeException();

}
