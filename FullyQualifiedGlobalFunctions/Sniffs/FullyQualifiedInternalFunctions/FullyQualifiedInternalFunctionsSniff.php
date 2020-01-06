<?php declare( strict_types = 1 );
/**
 * Summary of namespace Soderlind\Sniffs
 */

namespace Soderlind\FullyQualifiedGlobalFunctions\Sniffs;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

final class FullyQualifiedGlobalFunctionsSniff implements Sniff {

	private $fixer;
	private $stackPtr;
	private $globalFunctions = [];

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

}
