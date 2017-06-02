<?php 
namespace TYPO3\Eel\FlowQuery;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Eel".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/*
WARNING: This file has been machine generated. Do not edit it, or your changes will be overwritten next time it is compiled.
*/

/**
 * Fizzle parser
 *
 * This is the parser for a CSS-like selector language for Objects and TYPO3CR Nodes.
 * You can think of it as "Sizzle for PHP" (hence the name).
 */
class FizzleParser_Original extends \TYPO3\Eel\AbstractParser {
/* ObjectIdentifier: / [0-9a-zA-Z_-]+ / */
protected $match_ObjectIdentifier_typestack = array('ObjectIdentifier');
function match_ObjectIdentifier ($stack = array()) {
	$matchrule = "ObjectIdentifier"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/ [0-9a-zA-Z_-]+ /' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}




/* FilterGroup: :Filter ( S ',' S :Filter )* */
protected $match_FilterGroup_typestack = array('FilterGroup');
function match_FilterGroup ($stack = array()) {
	$matchrule = "FilterGroup"; $result = $this->construct($matchrule, $matchrule, null);
	$_8 = NULL;
	do {
		$matcher = 'match_'.'Filter'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "Filter" );
		}
		else { $_8 = FALSE; break; }
		while (true) {
			$res_7 = $result;
			$pos_7 = $this->pos;
			$_6 = NULL;
			do {
				$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_6 = FALSE; break; }
				if (substr($this->string,$this->pos,1) == ',') {
					$this->pos += 1;
					$result["text"] .= ',';
				}
				else { $_6 = FALSE; break; }
				$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) { $this->store( $result, $subres ); }
				else { $_6 = FALSE; break; }
				$matcher = 'match_'.'Filter'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "Filter" );
				}
				else { $_6 = FALSE; break; }
				$_6 = TRUE; break;
			}
			while(0);
			if( $_6 === FALSE) {
				$result = $res_7;
				$this->pos = $pos_7;
				unset( $res_7 );
				unset( $pos_7 );
				break;
			}
		}
		$_8 = TRUE; break;
	}
	while(0);
	if( $_8 === TRUE ) { return $this->finalise($result); }
	if( $_8 === FALSE) { return FALSE; }
}

function FilterGroup_Filter (&$result, $sub) {
		if (!isset($result['Filters'])) {
			$result['Filters'] = array();
		}
		$result['Filters'][] = $sub;
	}

/* Filter: ( :IdentifierFilter ) ? ( :PropertyNameFilter ) ?  ( AttributeFilters:AttributeFilter )* */
protected $match_Filter_typestack = array('Filter');
function match_Filter ($stack = array()) {
	$matchrule = "Filter"; $result = $this->construct($matchrule, $matchrule, null);
	$_19 = NULL;
	do {
		$res_12 = $result;
		$pos_12 = $this->pos;
		$_11 = NULL;
		do {
			$matcher = 'match_'.'IdentifierFilter'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "IdentifierFilter" );
			}
			else { $_11 = FALSE; break; }
			$_11 = TRUE; break;
		}
		while(0);
		if( $_11 === FALSE) {
			$result = $res_12;
			$this->pos = $pos_12;
			unset( $res_12 );
			unset( $pos_12 );
		}
		$res_15 = $result;
		$pos_15 = $this->pos;
		$_14 = NULL;
		do {
			$matcher = 'match_'.'PropertyNameFilter'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres, "PropertyNameFilter" );
			}
			else { $_14 = FALSE; break; }
			$_14 = TRUE; break;
		}
		while(0);
		if( $_14 === FALSE) {
			$result = $res_15;
			$this->pos = $pos_15;
			unset( $res_15 );
			unset( $pos_15 );
		}
		while (true) {
			$res_18 = $result;
			$pos_18 = $this->pos;
			$_17 = NULL;
			do {
				$matcher = 'match_'.'AttributeFilter'; $key = $matcher; $pos = $this->pos;
				$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
				if ($subres !== FALSE) {
					$this->store( $result, $subres, "AttributeFilters" );
				}
				else { $_17 = FALSE; break; }
				$_17 = TRUE; break;
			}
			while(0);
			if( $_17 === FALSE) {
				$result = $res_18;
				$this->pos = $pos_18;
				unset( $res_18 );
				unset( $pos_18 );
				break;
			}
		}
		$_19 = TRUE; break;
	}
	while(0);
	if( $_19 === TRUE ) { return $this->finalise($result); }
	if( $_19 === FALSE) { return FALSE; }
}

function Filter_AttributeFilters (&$result, $sub) {
		if (!isset($result['AttributeFilters'])) {
			$result['AttributeFilters'] = array();
		}
		$result['AttributeFilters'][] = $sub;
	}

function Filter_PropertyNameFilter (&$result, $sub) {
		$result['PropertyNameFilter'] = $sub['Identifier'];
	}

function Filter_IdentifierFilter (&$result, $sub) {
		$result['IdentifierFilter'] = substr($sub['text'], 1);
	}

/* PropertyNameFilter: Identifier */
protected $match_PropertyNameFilter_typestack = array('PropertyNameFilter');
function match_PropertyNameFilter ($stack = array()) {
	$matchrule = "PropertyNameFilter"; $result = $this->construct($matchrule, $matchrule, null);
	$matcher = 'match_'.'Identifier'; $key = $matcher; $pos = $this->pos;
	$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
	if ($subres !== FALSE) {
		$this->store( $result, $subres );
		return $this->finalise($result);
	}
	else { return FALSE; }
}

function PropertyNameFilter_Identifier (&$result, $sub) {
		$result['Identifier'] = $sub['text'];
	}

/* IdentifierFilter: '#':ObjectIdentifier */
protected $match_IdentifierFilter_typestack = array('IdentifierFilter');
function match_IdentifierFilter ($stack = array()) {
	$matchrule = "IdentifierFilter"; $result = $this->construct($matchrule, $matchrule, null);
	$_24 = NULL;
	do {
		if (substr($this->string,$this->pos,1) == '#') {
			$this->pos += 1;
			$result["text"] .= '#';
		}
		else { $_24 = FALSE; break; }
		$matcher = 'match_'.'ObjectIdentifier'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres, "ObjectIdentifier" );
		}
		else { $_24 = FALSE; break; }
		$_24 = TRUE; break;
	}
	while(0);
	if( $_24 === TRUE ) { return $this->finalise($result); }
	if( $_24 === FALSE) { return FALSE; }
}


/* AttributeFilter:
  '[' S
      (
          ( Operator:'instanceof' S ( Operand:StringLiteral | Operand:UnquotedOperand ) S )
          | ( :Identifier S
              (
                  Operator:( PrefixMatch | SuffixMatch | SubstringMatch | ExactMatch )
                  S ( Operand:StringLiteral | Operand:NumberLiteral | Operand:BooleanLiteral | Operand:UnquotedOperand ) S
              )?
          )
       )
  S ']' */
protected $match_AttributeFilter_typestack = array('AttributeFilter');
function match_AttributeFilter ($stack = array()) {
	$matchrule = "AttributeFilter"; $result = $this->construct($matchrule, $matchrule, null);
	$_85 = NULL;
	do {
		if (substr($this->string,$this->pos,1) == '[') {
			$this->pos += 1;
			$result["text"] .= '[';
		}
		else { $_85 = FALSE; break; }
		$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_85 = FALSE; break; }
		$_81 = NULL;
		do {
			$_79 = NULL;
			do {
				$res_28 = $result;
				$pos_28 = $this->pos;
				$_39 = NULL;
				do {
					$stack[] = $result; $result = $this->construct( $matchrule, "Operator" );
					if (( $subres = $this->literal( 'instanceof' ) ) !== FALSE) {
						$result["text"] .= $subres;
						$subres = $result; $result = array_pop($stack);
						$this->store( $result, $subres, 'Operator' );
					}
					else {
						$result = array_pop($stack);
						$_39 = FALSE; break;
					}
					$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) { $this->store( $result, $subres ); }
					else { $_39 = FALSE; break; }
					$_36 = NULL;
					do {
						$_34 = NULL;
						do {
							$res_31 = $result;
							$pos_31 = $this->pos;
							$matcher = 'match_'.'StringLiteral'; $key = $matcher; $pos = $this->pos;
							$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
							if ($subres !== FALSE) {
								$this->store( $result, $subres, "Operand" );
								$_34 = TRUE; break;
							}
							$result = $res_31;
							$this->pos = $pos_31;
							$matcher = 'match_'.'UnquotedOperand'; $key = $matcher; $pos = $this->pos;
							$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
							if ($subres !== FALSE) {
								$this->store( $result, $subres, "Operand" );
								$_34 = TRUE; break;
							}
							$result = $res_31;
							$this->pos = $pos_31;
							$_34 = FALSE; break;
						}
						while(0);
						if( $_34 === FALSE) { $_36 = FALSE; break; }
						$_36 = TRUE; break;
					}
					while(0);
					if( $_36 === FALSE) { $_39 = FALSE; break; }
					$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) { $this->store( $result, $subres ); }
					else { $_39 = FALSE; break; }
					$_39 = TRUE; break;
				}
				while(0);
				if( $_39 === TRUE ) { $_79 = TRUE; break; }
				$result = $res_28;
				$this->pos = $pos_28;
				$_77 = NULL;
				do {
					$matcher = 'match_'.'Identifier'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) {
						$this->store( $result, $subres, "Identifier" );
					}
					else { $_77 = FALSE; break; }
					$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
					$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
					if ($subres !== FALSE) { $this->store( $result, $subres ); }
					else { $_77 = FALSE; break; }
					$res_76 = $result;
					$pos_76 = $this->pos;
					$_75 = NULL;
					do {
						$stack[] = $result; $result = $this->construct( $matchrule, "Operator" );
						$_56 = NULL;
						do {
							$_54 = NULL;
							do {
								$res_43 = $result;
								$pos_43 = $this->pos;
								$matcher = 'match_'.'PrefixMatch'; $key = $matcher; $pos = $this->pos;
								$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
								if ($subres !== FALSE) {
									$this->store( $result, $subres );
									$_54 = TRUE; break;
								}
								$result = $res_43;
								$this->pos = $pos_43;
								$_52 = NULL;
								do {
									$res_45 = $result;
									$pos_45 = $this->pos;
									$matcher = 'match_'.'SuffixMatch'; $key = $matcher; $pos = $this->pos;
									$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
									if ($subres !== FALSE) {
										$this->store( $result, $subres );
										$_52 = TRUE; break;
									}
									$result = $res_45;
									$this->pos = $pos_45;
									$_50 = NULL;
									do {
										$res_47 = $result;
										$pos_47 = $this->pos;
										$matcher = 'match_'.'SubstringMatch'; $key = $matcher; $pos = $this->pos;
										$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
										if ($subres !== FALSE) {
											$this->store( $result, $subres );
											$_50 = TRUE; break;
										}
										$result = $res_47;
										$this->pos = $pos_47;
										$matcher = 'match_'.'ExactMatch'; $key = $matcher; $pos = $this->pos;
										$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
										if ($subres !== FALSE) {
											$this->store( $result, $subres );
											$_50 = TRUE; break;
										}
										$result = $res_47;
										$this->pos = $pos_47;
										$_50 = FALSE; break;
									}
									while(0);
									if( $_50 === TRUE ) { $_52 = TRUE; break; }
									$result = $res_45;
									$this->pos = $pos_45;
									$_52 = FALSE; break;
								}
								while(0);
								if( $_52 === TRUE ) { $_54 = TRUE; break; }
								$result = $res_43;
								$this->pos = $pos_43;
								$_54 = FALSE; break;
							}
							while(0);
							if( $_54 === FALSE) { $_56 = FALSE; break; }
							$_56 = TRUE; break;
						}
						while(0);
						if( $_56 === TRUE ) {
							$subres = $result; $result = array_pop($stack);
							$this->store( $result, $subres, 'Operator' );
						}
						if( $_56 === FALSE) {
							$result = array_pop($stack);
							$_75 = FALSE; break;
						}
						$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
						$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
						if ($subres !== FALSE) {
							$this->store( $result, $subres );
						}
						else { $_75 = FALSE; break; }
						$_72 = NULL;
						do {
							$_70 = NULL;
							do {
								$res_59 = $result;
								$pos_59 = $this->pos;
								$matcher = 'match_'.'StringLiteral'; $key = $matcher; $pos = $this->pos;
								$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
								if ($subres !== FALSE) {
									$this->store( $result, $subres, "Operand" );
									$_70 = TRUE; break;
								}
								$result = $res_59;
								$this->pos = $pos_59;
								$_68 = NULL;
								do {
									$res_61 = $result;
									$pos_61 = $this->pos;
									$matcher = 'match_'.'NumberLiteral'; $key = $matcher; $pos = $this->pos;
									$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
									if ($subres !== FALSE) {
										$this->store( $result, $subres, "Operand" );
										$_68 = TRUE; break;
									}
									$result = $res_61;
									$this->pos = $pos_61;
									$_66 = NULL;
									do {
										$res_63 = $result;
										$pos_63 = $this->pos;
										$matcher = 'match_'.'BooleanLiteral'; $key = $matcher; $pos = $this->pos;
										$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
										if ($subres !== FALSE) {
											$this->store( $result, $subres, "Operand" );
											$_66 = TRUE; break;
										}
										$result = $res_63;
										$this->pos = $pos_63;
										$matcher = 'match_'.'UnquotedOperand'; $key = $matcher; $pos = $this->pos;
										$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
										if ($subres !== FALSE) {
											$this->store( $result, $subres, "Operand" );
											$_66 = TRUE; break;
										}
										$result = $res_63;
										$this->pos = $pos_63;
										$_66 = FALSE; break;
									}
									while(0);
									if( $_66 === TRUE ) { $_68 = TRUE; break; }
									$result = $res_61;
									$this->pos = $pos_61;
									$_68 = FALSE; break;
								}
								while(0);
								if( $_68 === TRUE ) { $_70 = TRUE; break; }
								$result = $res_59;
								$this->pos = $pos_59;
								$_70 = FALSE; break;
							}
							while(0);
							if( $_70 === FALSE) { $_72 = FALSE; break; }
							$_72 = TRUE; break;
						}
						while(0);
						if( $_72 === FALSE) { $_75 = FALSE; break; }
						$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
						$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
						if ($subres !== FALSE) {
							$this->store( $result, $subres );
						}
						else { $_75 = FALSE; break; }
						$_75 = TRUE; break;
					}
					while(0);
					if( $_75 === FALSE) {
						$result = $res_76;
						$this->pos = $pos_76;
						unset( $res_76 );
						unset( $pos_76 );
					}
					$_77 = TRUE; break;
				}
				while(0);
				if( $_77 === TRUE ) { $_79 = TRUE; break; }
				$result = $res_28;
				$this->pos = $pos_28;
				$_79 = FALSE; break;
			}
			while(0);
			if( $_79 === FALSE) { $_81 = FALSE; break; }
			$_81 = TRUE; break;
		}
		while(0);
		if( $_81 === FALSE) { $_85 = FALSE; break; }
		$matcher = 'match_'.'S'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_85 = FALSE; break; }
		if (substr($this->string,$this->pos,1) == ']') {
			$this->pos += 1;
			$result["text"] .= ']';
		}
		else { $_85 = FALSE; break; }
		$_85 = TRUE; break;
	}
	while(0);
	if( $_85 === TRUE ) { return $this->finalise($result); }
	if( $_85 === FALSE) { return FALSE; }
}

function AttributeFilter__construct (&$result) {
		$result['Operator'] = NULL;
		$result['Identifier'] = NULL;
	}

function AttributeFilter_Identifier (&$result, $sub) {
		$result['Identifier'] = $sub['text'];
	}

function AttributeFilter_Operator (&$result, $sub) {
		$result['Operator'] = $sub['text'];
	}

function AttributeFilter_Operand (&$result, $sub) {
		$result['Operand'] = $sub['val'];
	}

/* UnquotedOperand: / [^"'\[\]\s]+ / */
protected $match_UnquotedOperand_typestack = array('UnquotedOperand');
function match_UnquotedOperand ($stack = array()) {
	$matchrule = "UnquotedOperand"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/ [^"\'\[\]\s]+ /' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}

function UnquotedOperand__finalise (&$self) {
		$self['val'] = $self['text'];
	}

/* PrefixMatch: '^=' */
protected $match_PrefixMatch_typestack = array('PrefixMatch');
function match_PrefixMatch ($stack = array()) {
	$matchrule = "PrefixMatch"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->literal( '^=' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* SuffixMatch: '$=' */
protected $match_SuffixMatch_typestack = array('SuffixMatch');
function match_SuffixMatch ($stack = array()) {
	$matchrule = "SuffixMatch"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->literal( '$=' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* SubstringMatch: '*=' */
protected $match_SubstringMatch_typestack = array('SubstringMatch');
function match_SubstringMatch ($stack = array()) {
	$matchrule = "SubstringMatch"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->literal( '*=' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* ExactMatch: '=' */
protected $match_ExactMatch_typestack = array('ExactMatch');
function match_ExactMatch ($stack = array()) {
	$matchrule = "ExactMatch"; $result = $this->construct($matchrule, $matchrule, null);
	if (substr($this->string,$this->pos,1) == '=') {
		$this->pos += 1;
		$result["text"] .= '=';
		return $this->finalise($result);
	}
	else { return FALSE; }
}




	static public function parseFilterGroup($filter) {
		$parser = new FizzleParser($filter);
		$parsedFilter = $parser->match_FilterGroup();
		if ($parser->pos !== strlen($filter)) {
			throw new FizzleException(sprintf('The Selector "%s" could not be parsed. Error at character %d.', $filter, $parser->pos+1), 1327649317);
		}
		return $parsedFilter;
	}

	function BooleanLiteral__finalise(&$self) {
		$self['val'] = strtolower($self['text']) === 'true';
	}

	public function NumberLiteral__finalise(&$self) {
		if (isset($self['dec'])) {
			$self['val'] = (float)($self['text']);
		} else {
			$self['val'] = (integer)$self['text'];
		}
	}
}
namespace TYPO3\Eel\FlowQuery;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Fizzle parser
 * 
 * This is the parser for a CSS-like selector language for Objects and TYPO3CR Nodes.
 * You can think of it as "Sizzle for PHP" (hence the name).
 */
class FizzleParser extends FizzleParser_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		$arguments = func_get_args();

		if (!array_key_exists(0, $arguments)) $arguments[0] = NULL;
		if (!array_key_exists(0, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $string in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
		call_user_func_array('parent::__construct', $arguments);
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {

	if (property_exists($this, 'Flow_Persistence_RelatedEntities') && is_array($this->Flow_Persistence_RelatedEntities)) {
		$persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		foreach ($this->Flow_Persistence_RelatedEntities as $entityInformation) {
			$entity = $persistenceManager->getObjectByIdentifier($entityInformation['identifier'], $entityInformation['entityType'], TRUE);
			if (isset($entityInformation['entityPath'])) {
				$this->$entityInformation['propertyName'] = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->$entityInformation['propertyName'], $entityInformation['entityPath'], $entity);
			} else {
				$this->$entityInformation['propertyName'] = $entity;
			}
		}
		unset($this->Flow_Persistence_RelatedEntities);
	}
			}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$this->Flow_Object_PropertiesToSerialize = array();
	$reflectionService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('TYPO3\Eel\FlowQuery\FizzleParser');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Eel\FlowQuery\FizzleParser', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
		if (is_array($this->$propertyName) || (is_object($this->$propertyName) && ($this->$propertyName instanceof \ArrayObject || $this->$propertyName instanceof \SplObjectStorage ||$this->$propertyName instanceof \Doctrine\Common\Collections\Collection))) {
			if (count($this->$propertyName) > 0) {
				foreach ($this->$propertyName as $key => $value) {
					$this->searchForEntitiesAndStoreIdentifierArray((string)$key, $value, $propertyName);
				}
			}
		}
		if (is_object($this->$propertyName) && !$this->$propertyName instanceof \Doctrine\Common\Collections\Collection) {
			if ($this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($this->$propertyName);
			} else {
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Eel\FlowQuery\FizzleParser', $propertyName, 'var');
				if (count($varTagValues) > 0) {
					$className = trim($varTagValues[0], '\\');
				}
				if (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->isRegistered($className) === FALSE) {
					$className = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($this->$propertyName));
				}
			}
			if ($this->$propertyName instanceof \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->isNewObject($this->$propertyName) || $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				if (!property_exists($this, 'Flow_Persistence_RelatedEntities') || !is_array($this->Flow_Persistence_RelatedEntities)) {
					$this->Flow_Persistence_RelatedEntities = array();
					$this->Flow_Object_PropertiesToSerialize[] = 'Flow_Persistence_RelatedEntities';
				}
				$identifier = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->getIdentifierByObject($this->$propertyName);
				if (!$identifier && $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
					$identifier = current(\TYPO3\Flow\Reflection\ObjectAccess::getProperty($this->$propertyName, '_identifier', TRUE));
				}
				$this->Flow_Persistence_RelatedEntities[$propertyName] = array(
					'propertyName' => $propertyName,
					'entityType' => $className,
					'identifier' => $identifier
				);
				continue;
			}
			if ($className !== FALSE && (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getScope($className) === \TYPO3\Flow\Object\Configuration\Configuration::SCOPE_SINGLETON || $className === 'TYPO3\Flow\Object\DependencyInjection\DependencyProxy')) {
				continue;
			}
		}
		$this->Flow_Object_PropertiesToSerialize[] = $propertyName;
	}
	$result = $this->Flow_Object_PropertiesToSerialize;
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function searchForEntitiesAndStoreIdentifierArray($path, $propertyValue, $originalPropertyName) {

		if (is_array($propertyValue) || (is_object($propertyValue) && ($propertyValue instanceof \ArrayObject || $propertyValue instanceof \SplObjectStorage))) {
			foreach ($propertyValue as $key => $value) {
				$this->searchForEntitiesAndStoreIdentifierArray($path . '.' . $key, $value, $originalPropertyName);
			}
		} elseif ($propertyValue instanceof \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->isNewObject($propertyValue) || $propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
			if (!property_exists($this, 'Flow_Persistence_RelatedEntities') || !is_array($this->Flow_Persistence_RelatedEntities)) {
				$this->Flow_Persistence_RelatedEntities = array();
				$this->Flow_Object_PropertiesToSerialize[] = 'Flow_Persistence_RelatedEntities';
			}
			if ($propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($propertyValue);
			} else {
				$className = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($propertyValue));
			}
			$identifier = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->getIdentifierByObject($propertyValue);
			if (!$identifier && $propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
				$identifier = current(\TYPO3\Flow\Reflection\ObjectAccess::getProperty($propertyValue, '_identifier', TRUE));
			}
			$this->Flow_Persistence_RelatedEntities[$originalPropertyName . '.' . $path] = array(
				'propertyName' => $originalPropertyName,
				'entityType' => $className,
				'identifier' => $identifier,
				'entityPath' => $path
			);
			$this->$originalPropertyName = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->$originalPropertyName, $path, NULL);
		}
			}
}
#