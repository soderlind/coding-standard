<?php declare( strict_types = 1 );
/**
 * Summary of namespace Soderlind\Sniffs
 */

namespace Soderlind\FullyQualifiedGlobalFunctions\Sniffs;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Config;


final class FullyQualifiedGlobalFunctionsSniff implements Sniff {

	private $sniff = 'FullyQualifiedGlobalFunctions';
	private $fixer;
	private $stackPtr;
	private $globalFunctions        = [];
	private $onlyOptimizedFunctions = null;
	private $optimizedFunctions     = [
		// @see https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_compile.c "zend_try_compile_special_func"
		'array_key_exists'     => true,
		'array_slice'          => true,
		'assert'               => true,
		'boolval'              => true,
		'call_user_func'       => true,
		'call_user_func_array' => true,
		'chr'                  => true,
		'count'                => true,
		'defined'              => true,
		'doubleval'            => true,
		'floatval'             => true,
		'func_get_args'        => true,
		'func_num_args'        => true,
		'get_called_class'     => true,
		'get_class'            => true,
		'gettype'              => true,
		'in_array'             => true,
		'intval'               => true,
		'is_array'             => true,
		'is_bool'              => true,
		'is_double'            => true,
		'is_float'             => true,
		'is_int'               => true,
		'is_integer'           => true,
		'is_long'              => true,
		'is_null'              => true,
		'is_object'            => true,
		'is_real'              => true,
		'is_resource'          => true,
		'is_string'            => true,
		'ord'                  => true,
		'strlen'               => true,
		'strval'               => true,
		// @see https://github.com/php/php-src/blob/php-7.2.6/ext/opcache/Optimizer/pass1_5.c
		'constant'             => true,
		'define'               => true,
		'dirname'              => true,
		'extension_loaded'     => true,
		'function_exists'      => true,
		'is_callable'          => true,
	];

	/**
	 * Returns an array of tokens this test wants to listen for.
	 * We're looking for all functions, so use T_STRING.
	 *
	 * @return array
	 */
	public function register(): array {

		$this->globalFunctions = \array_flip( \get_defined_functions()['internal'] );

		return [ \T_STRING ];
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * Code from ForbiddenFunctionsSniff:
	 *
	 * @link https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Sniffs/PHP/ForbiddenFunctionsSniff.php#L118
	 *
	 * @param File $phpcsFile
	 * @param int  $stackPtr
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {

		if ( $this->onlyOptimizedFunctions === null ) {
			$this->onlyOptimizedFunctions = $this->get_ruleset_property( $phpcsFile, 'onlyOptimizedFunctions' );
		}

		if ( null !== $this->onlyOptimizedFunctions && false !== \filter_var( $this->onlyOptimizedFunctions, FILTER_VALIDATE_BOOLEAN ) ) {
			$this->globalFunctions = $this->optimizedFunctions;
		}

		$this->stackPtr = $stackPtr;
		$this->fixer    = $phpcsFile->fixer;
		$tokens         = $phpcsFile->getTokens();
		$ignore         = [
			T_DOUBLE_COLON    => true,
			T_OBJECT_OPERATOR => true,
			T_FUNCTION        => true,
			T_CONST           => true,
			T_PUBLIC          => true,
			T_PRIVATE         => true,
			T_PROTECTED       => true,
			T_AS              => true,
			T_NEW             => true,
			T_INSTEADOF       => true,
			T_NS_SEPARATOR    => true,
			T_IMPLEMENTS      => true,
		];
		$prevToken      = $phpcsFile->findPrevious( T_WHITESPACE, ( $stackPtr - 1 ), null, true );

		// If function call is directly preceded by a NS_SEPARATOR don't try to fix it.
		if ( $tokens[ $prevToken ]['code'] === T_NS_SEPARATOR && $tokens[ $stackPtr ]['code'] === T_STRING ) {
				return;
		}
		if ( isset( $ignore[ $tokens[ $prevToken ]['code'] ] ) === true ) {
			// Not a call to a PHP function.
			return;
		}
		$nextToken = $phpcsFile->findNext( T_WHITESPACE, ( $stackPtr + 1 ), null, true );
		if ( isset( $ignore[ $tokens[ $nextToken ]['code'] ] ) === true ) {
			// Not a call to a PHP function.
			return;
		}
		if ( $tokens[ $stackPtr ]['code'] === T_STRING && $tokens[ $nextToken ]['code'] !== T_OPEN_PARENTHESIS ) {
			// Not a call to a PHP function.
			return;
		}
		$function = \strtolower( $tokens[ $stackPtr ]['content'] );

		// Is it an global PHP function?
		if ( false !== isset( $this->globalFunctions[ $function ] ) ) {

			$error = \sprintf( 'Function %1$s() should be referenced via a fully qualified name, e.g.: \%1$s()', $function );
			$fix   = $phpcsFile->addFixableError( $error, $stackPtr, 'FullyQualifiedGlobalFunctions' );

			if ( true === $fix ) {
				$this->fix( $function );
			}
		}

	}

	/**
	 * Prepend the global PHP function with backslash.
	 *
	 * @param string $function Function name.
	 * @return void
	 */
	private function fix( string $function ): void {
		$this->fixer->replaceToken( $this->stackPtr, '\\' . $function );
	}

	/**
	 * Get ruleset property
	 *
	 * - Check if the property is set on command line (--runtime-set <PROPERTY> <VALUE>), will override property in ruleset file.
	 * - or, check if the property is set in the rulset file.
	 *
	 * @param File   $phpcsFile
	 * @param string $property Property name.
	 * @return mixed
	 */
	private function get_ruleset_property( File $phpcsFile, string $property ) {

		print_r( $phpcsFile->ruleset->ruleset );

		if ( null !== Config::getConfigData( $property ) ) {
			return Config::getConfigData( $property );
		} elseif ( isset( $phpcsFile->ruleset->ruleset[ $this->sniff ] ) === true && isset( $phpcsFile->ruleset->ruleset[ $this->sniff ]['properties'] ) === true && isset( $phpcsFile->ruleset->ruleset[ $this->sniff ]['properties'][ $property ] ) ) {
			return $phpcsFile->ruleset->ruleset[ $this->sniff ]['properties'][ $property ];
		} else {
			return null;
		}
	}

}
