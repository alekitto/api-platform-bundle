<?php declare(strict_types=1);

namespace Fazland\ApiPlatformBundle\QueryLanguage\Grammar;

use Fazland\ApiPlatformBundle\QueryLanguage\Exception\InvalidArgumentException;
use Fazland\ApiPlatformBundle\QueryLanguage\Exception\SyntaxError;
use Fazland\ApiPlatformBundle\QueryLanguage\Expression;

abstract class AbstractGrammar
{
    private const YYERRTOK = 256;
    private const ALL = 257;
    private const STRING = 258;
    private const NOT = 259;
    private const EQ = 260;
    private const NEQ = 261;
    private const LIKE = 262;
    private const LT = 263;
    private const LTE = 264;
    private const GT = 265;
    private const GTE = 266;
    private const ORDER = 267;
    private const RANGE = 268;
    private const AND_OP = 269;
    private const OR_OP = 270;
    private const IN_OP = 271;
    private const ORDER_DIRECTION = 272;
    private const ENTRY = 273;

    private const YYBADCH = 22;
    private const YYMAXLEX = 274;
    private const YYTERMS = 22;
    private const YYNONTERMS = 14;

    private const YYLAST = 51;
    private const YY2TBLSTATE = 6;
    private const YYGLAST = 18;


    private const YYSTATES = 71;
    private const YYNLSTATES = 32;
    private const YYINTERRTOK = 1;
    private const YYUNEXPECTED = 32767;
    private const YYDEFAULT = -32766;

    private $buffer;
    private $token;
    private $toktype;

    private $yyastk;

/*
  #define yyclearin (yychar = -1)
  #define yyerrok (yyerrflag = 0)
  #define YYRECOVERING (yyerrflag != 0)
  #define YYERROR  goto yyerrlab
*/

    /** Debug mode flag **/
    private $yydebug = false;

    /** lexical element object **/
    private $yylval;


    private $yytranslate = [
            0,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           19,   20,   22,   22,   21,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,   22,   22,   22,   22,
           22,   22,   22,   22,   22,   22,    1,    2,    3,    4,
            5,    6,    7,    8,    9,   10,   11,   12,   13,   14,
           15,   16,   17,   18
    ];

    private $yyaction = [
           43,   29,   33,   34,   35,   36,   37,   38,   39,   43,
           16,   40,   41,   42,   44,   17,   13,   13,   14,   14,
            0,   65,    5,   44,   54,   28,   15,   15,    3,    1,
            4,    7,    8,    6,    0,    9,   21,    6,    0,   47,
            0,   49,   46,   55,   53,   52,   51,   50,    0,   10,
            2
    ];

    private $yycheck = [
            3,   17,    5,    6,    7,    8,    9,   10,   11,    3,
           13,   14,   15,   16,   17,   18,    2,    2,    4,    4,
            0,   20,   21,   17,   20,   21,   12,   12,   19,   19,
           19,   19,   19,   19,   -1,   19,   19,   19,   -1,   20,
           -1,   20,   20,   20,   20,   20,   20,   20,   -1,   21,
           21
    ];

    private $yybase = [
           14,   14,   14,   15,   18,   18,    6,    6,    6,    6,
            6,    4,    1,   17,   10,   12,   13,   16,   20,    9,
           11,   22,   21,   19,   28,   29,   27,   26,  -16,   23,
           24,   25,   -3,   -3,   -3,   -3,   -3,   -3
    ];

    private $yydefault = [
           40,   40,   40,32767,   24,32767,32767,32767,32767,32767,
        32767,32767,32767,   13,32767,32767,32767,32767,32767,32767,
        32767,32767,32767,32767,32767,32767,32767,32767,32767,32767,
        32767,32767
    ];

    private $yygoto = [
           26,   27,    0,   22,   11,   24,   25,   30,   23,   31,
           60,   64,   57,   61,   58,   62,   59,   63
    ];

    private $yygcheck = [
            4,    9,   -1,    4,    4,    4,    4,    4,    1,    1,
           13,   13,    7,    7,    8,    8,   10,   10
    ];

    private $yygbase = [
            0,    7,    0,    0,   -3,    0,    0,    8,   10,   -2,
           12,    0,    0,    6
    ];

    private $yygdefault = [
        -32768,   18,   19,   20,   48,   66,   67,   73,   68,   74,
           69,   70,   12,   71
    ];

    private $yylhs = [
            0,    2,    2,    2,    2,    2,    2,    2,    3,    3,
            3,    4,    4,    5,    5,    6,    7,    7,    8,    8,
           10,   10,   11,   11,   12,   12,   12,   12,   12,   12,
           12,   12,   12,   13,    9,    9,    9,    9,    9,    9,
            1,    1,    1
    ];

    private $yylen = [
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    3,    4,    1,    3,    4,    4,
            6,    6,    4,    6,    0,    1,    1,    1,    1,    3,
            3,    3,    3,    4,    1,    1,    1,    1,    1,    1,
            0,    1,    1
    ];

    public function __construct()
    {
    }

    public function parse(string $input)
    {
        $this->__lex_buffer = $this->buffer = $input;

        try {
            $this->yyparse();
        } catch (InvalidArgumentException $e) {
            $e->setMessage(sprintf("Expression \"%s\" is invalid.\n".$e->getMessage(), $this->buffer));
            throw $e;
        }

        return reset($this->yyastk);
    }

    private function yyflush()
    {
        return;
    }

    private function yytokname(int $n): string
    {
        switch ($n) {
            case 0: return "EOF";
            case 256: return "error";
            case 257: return "ALL";
            case 258: return "STRING";
            case 259: return "NOT";
            case 260: return "EQ";
            case 261: return "NEQ";
            case 262: return "LIKE";
            case 263: return "LT";
            case 264: return "LTE";
            case 265: return "GT";
            case 266: return "GTE";
            case 267: return "ORDER";
            case 268: return "RANGE";
            case 269: return "AND_OP";
            case 270: return "OR_OP";
            case 271: return "IN_OP";
            case 272: return "ORDER_DIRECTION";
            case 273: return "ENTRY";
            case 40: return "'('";
            case 41: return "')'";
            case 44: return "','";
            default:
                return "???";
        }
    }


    private function yyerror(): void
    {
        $position = $this->__lex_buffer ? strpos($this->buffer, $this->__lex_buffer) : strlen($this->buffer);

        throw new SyntaxError($this->buffer, $position);
    }

    private function yyparse()
    {
        $this->yyastk = [];
        $yyastk = &$this->yyastk;
        $yysstk = [];

        $yyn = $yyl = 0;
        $yystate = 0;
        $yychar = -1;

        $yysp = 0;
        $yysstk[$yysp] = 0;
        $yyerrflag = 0;
        while (true) {
            if ($this->yybase[$yystate] == 0) {
                $yyn = $this->yydefault[$yystate];
            } elseif ($yychar < 0) {
                if (($yychar = $this->yylex()) <= 0) {
                    $yychar = 0;
                }
                $yychar = $yychar < self::YYMAXLEX ? $this->yytranslate[$yychar] : self::YYBADCH;
            }

            if ((($yyn = $this->yybase[$yystate] + $yychar) >= 0
                    && $yyn < self::YYLAST && $this->yycheck[$yyn] == $yychar
                    || ($yystate < self::YY2TBLSTATE
                        && ($yyn = $this->yybase[$yystate + self::YYNLSTATES] + $yychar) >= 0
                        && $yyn < self::YYLAST && $this->yycheck[$yyn] == $yychar))
                && ($yyn = $this->yyaction[$yyn]) != self::YYDEFAULT) {
                /*
                 * >= YYNLSTATE: shift and reduce
                 * > 0: shift
                 * = 0: accept
                 * < 0: reduce
                 * = -YYUNEXPECTED: error
                 */
                if ($yyn > 0) {
                    /* shift */
                    $yysp++;

                    $yysstk[$yysp] = $yystate = $yyn;
                    $yyastk[$yysp] = $this->yylval;
                    $yychar = -1;

                    if ($yyerrflag > 0) {
                        $yyerrflag--;
                    }

                    if ($yyn < self::YYNLSTATES) {
                        continue;
                    }

                    /* $yyn >= YYNLSTATES means shift-and-reduce */
                    $yyn -= self::YYNLSTATES;
                } else {
                    $yyn = -$yyn;
                }
            } else {
                $yyn = $this->yydefault[$yystate];
            }

            while (true) {
                /* reduce/error */
                if ($yyn == 0) {
                    /* accept */
                    $this->yyflush();
                    return 0;
                } elseif ($yyn != self::YYUNEXPECTED) {
                    /* reduce */
                    $yyl = $this->yylen[$yyn];
                    $n = $yysp-$yyl+1;
                    $yyval = isset($yyastk[$n]) ? $yyastk[$n] : null;
                    /* Following line will be replaced by reduce actions */
                    switch($yyn) {
                        case 11:
                            { $yyval = $yyastk[$yysp-(1-1)]; } break;
                        case 12:
                            { $yyval = $yyastk[$yysp-(1-1)]; } break;
                        case 13:
                            { $yyval = $this->unaryExpression('all', null); } break;
                        case 14:
                            { $yyval = $this->unaryExpression('all', null); } break;
                        case 15:
                            { $yyval = $this->unaryExpression('not', $yyastk[$yysp-(4-3)]); } break;
                        case 16:
                            { $yyval = $this->unaryExpression('eq', Expression\Literal\LiteralExpression::create($yyastk[$yysp-(1-1)])); } break;
                        case 17:
                            { $yyval = $this->unaryExpression('eq', Expression\Literal\LiteralExpression::create($yyastk[$yysp-(3-2)])); } break;
                        case 18:
                            { $yyval = $this->unaryExpression($yyastk[$yysp-(4-1)], Expression\Literal\LiteralExpression::create($yyastk[$yysp-(4-3)])); } break;
                        case 19:
                            { $yyval = $this->unaryExpression($yyastk[$yysp-(4-1)], $yyastk[$yysp-(4-3)]); } break;
                        case 20:
                            { $yyval = $this->binaryExpression($yyastk[$yysp-(6-1)], Expression\Literal\LiteralExpression::create($yyastk[$yysp-(6-3)]), $yyastk[$yysp-(6-5)]); } break;
                        case 21:
                            { $yyval = $this->binaryExpression($yyastk[$yysp-(6-1)], Expression\Literal\LiteralExpression::create($yyastk[$yysp-(6-3)]), Expression\Literal\LiteralExpression::create($yyastk[$yysp-(6-5)])); } break;
                        case 22:
                            { $yyval = $this->orderExpression($yyastk[$yysp-(4-3)], 'asc'); } break;
                        case 23:
                            { $yyval = $this->orderExpression($yyastk[$yysp-(6-3)], $yyastk[$yysp-(6-5)]); } break;
                        case 24:
                            { $yyval = []; } break;
                        case 25:
                            { $yyval = [ $yyastk[$yysp-(1-1)] ]; } break;
                        case 26:
                            { $yyval = [ $yyastk[$yysp-(1-1)] ]; } break;
                        case 27:
                            { $yyval = [ $yyastk[$yysp-(1-1)] ]; } break;
                        case 28:
                            { $yyval = [ $yyastk[$yysp-(1-1)] ]; } break;
                        case 29:
                            { $yyval[] = $yyastk[$yysp-(3-3)]; } break;
                        case 30:
                            { $yyval[] = $yyastk[$yysp-(3-3)]; } break;
                        case 31:
                            { $yyval[] = $yyastk[$yysp-(3-3)]; } break;
                        case 32:
                            { $yyval[] = $yyastk[$yysp-(3-3)]; } break;
                        case 33:
                            { $yyval = $this->variadicExpression($yyastk[$yysp-(4-1)], $yyastk[$yysp-(4-3)]); } break;
                    }

                    /* Goto - shift nonterminal */
                    $yysp -= $yyl;
                    $yyn = $this->yylhs[$yyn];
                    if (($yyp = $this->yygbase[$yyn] + $yysstk[$yysp]) >= 0 && $yyp < self::YYGLAST
                        && $this->yygcheck[$yyp] == $yyn) {
                        $yystate = $this->yygoto[$yyp];
                    } else {
                        $yystate = $this->yygdefault[$yyn];
                    }

                    $yysp++;

                    $yysstk[$yysp] = $yystate;
                    $yyastk[$yysp] = $yyval;
                } else {
                    /* error */
                    switch ($yyerrflag) {
                        case 0:
                            $this->yyerror();
                        case 1:
                        case 2:
                            $yyerrflag = 3;
                            /* Pop until error-expecting state uncovered */

                            while (!(($yyn = $this->yybase[$yystate] + self::YYINTERRTOK) >= 0
                                && $yyn < self::YYLAST && $this->yycheck[$yyn] == self::YYINTERRTOK
                                || ($yystate < self::YY2TBLSTATE
                                    && ($yyn = $this->yybase[$yystate + self::YYNLSTATES] + self::YYINTERRTOK) >= 0
                                    && $yyn < self::YYLAST && $this->yycheck[$yyn] == self::YYINTERRTOK))) {
                                if ($yysp <= 0) {
                                    $this->yyflush();

                                    return 1;
                                }

                                $yystate = $yysstk[--$yysp];
                            }

                            $yyn = $this->yyaction[$yyn];
                            $yysstk[++$yysp] = $yystate = $yyn;
                            break;

                        case 3:
                            if ($yychar == 0) {
                                $this->yyflush();

                                return 1;
                            }

                            $yychar = -1;
                            break;
                    }
                }

                if ($yystate < self::YYNLSTATES) {
                    break;
                }

                /* >= YYNLSTATES means shift-and-reduce */
                $yyn = $yystate - self::YYNLSTATES;
            }
        }
    }


private $__lex_buffer = '';
private const __QL_OPERATORS = [
    self::ALL => 'all',
    self::NOT => 'not',
    self::EQ => 'eq',
    self::NEQ => 'neq',
    self::LIKE => 'like',
    self::LTE => 'lte',
    self::LT => 'lt',
    self::GTE => 'gte',
    self::GT => 'gt',
    self::ORDER => 'order',
    self::AND_OP => 'and',
    self::OR_OP => 'or',
    self::IN_OP => 'in',
    self::RANGE => 'range',
    self::ENTRY => 'entry',
];

private function yylex(): int
{
    $this->__lex_buffer = preg_replace('/^\s+/', '', $this->__lex_buffer);
    if (preg_match('/^\s*(asc|desc)\s*/i', $this->__lex_buffer, $matches)) {
        $this->yylval = strtolower($matches[1]);
        $this->__lex_buffer = substr($this->__lex_buffer, strlen($matches[0]));

        return self::ORDER_DIRECTION;
    }

    if (preg_match('/^([^\(\)\$\n\r\0\t,]|(?<=\\\\)[\)\(\$])+/', $this->__lex_buffer, $matches)) {
        $this->yylval = trim($matches[0]);
        $this->__lex_buffer = substr($this->__lex_buffer, strlen($matches[0]));

        return self::STRING;
    }

    foreach (self::__QL_OPERATORS as $t => $val) {
        if (preg_match('/^\$'.$val.'/i', $this->__lex_buffer, $matches)) {
            $this->yylval = strtolower($val);
            $this->__lex_buffer = substr($this->__lex_buffer, strlen($matches[0]));

            return $t;
        }
    }

    if (! strlen($this->__lex_buffer)) {
        return 0;
    }

    $val = $this->__lex_buffer[0];
    $this->__lex_buffer = substr($this->__lex_buffer, 1);

    return ord($val);
}

/**
 * Evaluates an unary expression.
 *
 * @param string $type
 * @param mixed $value
 *
 * @return mixed
 */
abstract protected function unaryExpression(string $type, $value);

/**
 * Evaluates a binary expression.
 *
 * @param string $type
 * @param mixed $left
 * @param mixed $right
 *
 * @return mixed
 */
abstract protected function binaryExpression(string $type, $left, $right);

/**
 * Evaluates an order expression.
 *
 * @param string $field
 * @param string $direction
 *
 * @return mixed
 */
abstract protected function orderExpression(string $field, string $direction);

/**
 * Evaluates an expression with variadic arguments.
 *
 * @param string $type
 * @param mixed[] $arguments
 *
 * @return mixed
 */
abstract protected function variadicExpression(string $type, array $arguments);
}