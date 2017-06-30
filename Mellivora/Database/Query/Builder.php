<?php

namespace Mellivora\Database\Query;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use Mellivora\Database\ConnectionInterface;
use Mellivora\Database\Query\Grammars\Grammar;
use Mellivora\Database\Query\Processors\Processor;
use Mellivora\Pagination\LengthAwarePaginator;
use Mellivora\Pagination\Paginator;
use Mellivora\Support\Arr;
use Mellivora\Support\Collection;
use Mellivora\Support\Contracts\Arrayable;
use Mellivora\Support\Str;
use Mellivora\Support\Traits\Macroable;
use RuntimeException;

class Builder
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The database connection instance.
     *
     * @var \Mellivora\Database\Connection
     */
    public $connection;

    /**
     * The database query grammar instance.
     *
     * @var \Mellivora\Database\Query\Grammars\Grammar
     */
    public $grammar;

    /**
     * The database query post processor instance.
     *
     * @var \Mellivora\Database\Query\Processors\Processor
     */
    public $processor;

    /**
     * The current query value bindings.
     *
     * @var array
     */
    public $bindings = [
        'select' => [],
        'join'   => [],
        'where'  => [],
        'having' => [],
        'order'  => [],
        'union'  => [],
    ];

    /**
     * An aggregate function and column to be run.
     *
     * @var array
     */
    public $aggregate;

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    public $columns;

    /**
     * Indicates if the query returns distinct results.
     *
     * @var bool
     */
    public $distinct = false;

    /**
     * The table which the query is targeting.
     *
     * @var string
     */
    public $from;

    /**
     * The table joins for the query.
     *
     * @var array
     */
    public $joins;

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $wheres;

    /**
     * The groupings for the query.
     *
     * @var array
     */
    public $groups;

    /**
     * The having constraints for the query.
     *
     * @var array
     */
    public $havings;

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public $orders;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public $offset;

    /**
     * The query union statements.
     *
     * @var array
     */
    public $unions;

    /**
     * The maximum number of union records to return.
     *
     * @var int
     */
    public $unionLimit;

    /**
     * The number of union records to skip.
     *
     * @var int
     */
    public $unionOffset;

    /**
     * The orderings for the union query.
     *
     * @var array
     */
    public $unionOrders;

    /**
     * Indicates whether row locking is being used.
     *
     * @var string|bool
     */
    public $lock;

    /**
     * All of the available clause operators.
     *
     * @var array
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'like binary', 'not like', 'between', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    /**
     * Whether use write pdo for select.
     *
     * @var bool
     */
    public $useWritePdo = false;

    /**
     * Create a new query builder instance.
     *
     * @param  \Mellivora\Database\ConnectionInterface        $connection
     * @param  \Mellivora\Database\Query\Grammars\Grammar     $grammar
     * @param  \Mellivora\Database\Query\Processors\Processor $processor
     * @return void
     */
    function __construct(ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null) {
        $this->connection = $connection;
        $this->grammar    = $grammar ?: $connection->getQueryGrammar();
        $this->processor  = $processor ?: $connection->getPostProcessor();
    }

    /**
     * Set the columns to be selected.
     *
     * @param  array|mixed $columns
     * @return $this
     */
    function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Add a new "raw" select expression to the query.
     *
     * @param  string                                     $expression
     * @param  array                                      $bindings
     * @return \Mellivora\Database\Query\Builder|static
     */
    function selectRaw($expression, array $bindings = [])
    {
        $this->addSelect(new Expression($expression));

        if ($bindings) {
            $this->addBinding($bindings, 'select');
        }

        return $this;
    }

    /**
     * Add a subselect expression to the query.
     *
     * @param  \Closure|\Mellivora\Database\Query\Builder|string $query
     * @param  string                                            $as
     * @throws \InvalidArgumentException
     * @return \Mellivora\Database\Query\Builder|static
     */
    function selectSub($query, $as)
    {
        // If the given query is a Closure, we will execute it while passing in a new
        // query instance ot the Closure. This will give the developer a chance to
        // format and work with the query before we cast it to a raw SQL string.
        if ($query instanceof Closure) {
            $callback = $query;

            $callback($query = $this->newQuery());
        }

        // Here, we will parse this query into an SQL string and an array of bindings
        // so we can add it to the query builder using the selectRaw method so the
        // query is included in the real SQL generated by this builder instance.
        list($query, $bindings) = $this->parseSubSelect($query);

        return $this->selectRaw(
            '(' . $query . ') as ' . $this->grammar->wrap($as), $bindings
        );
    }

    /**
     * Parse the sub-select query into SQL and bindings.
     *
     * @param  mixed   $query
     * @return array
     */
    function parseSubSelect($query)
    {
        if ($query instanceof self) {
            return [$query->toSql(), $query->getBindings()];
        } elseif (is_string($query)) {
            return [$query, []];
        } else {
            throw new InvalidArgumentException;
        }
    }

    /**
     * Add a new select column to the query.
     *
     * @param  array|mixed $column
     * @return $this
     */
    function addSelect($column)
    {
        $column = is_array($column) ? $column : func_get_args();

        $this->columns = array_merge((array) $this->columns, $column);

        return $this;
    }

    /**
     * Force the query to only return distinct results.
     *
     * @return $this
     */
    function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param  string  $table
     * @return $this
     */
    function from($table)
    {
        $this->from = $table;

        return $this;
    }

    /**
     * Add a join clause to the query.
     *
     * @param  string  $table
     * @param  string  $first
     * @param  string  $operator
     * @param  string  $second
     * @param  string  $type
     * @param  bool    $where
     * @return $this
     */
    function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $join = new JoinClause($this, $type, $table);

        // If the first "column" of the join is really a Closure instance the developer
        // is trying to build a join with a complex "on" clause containing more than
        // one condition, so we'll add the join and call a Closure with the query.
        if ($first instanceof Closure) {
            call_user_func($first, $join);

            $this->joins[] = $join;

            $this->addBinding($join->getBindings(), 'join');
        }

        // If the column is simply a string, we can assume the join simply has a basic
        // "on" clause with a single condition. So we will just build the join with
        // this simple join clauses attached to it. There is not a join callback.
        else {
            $method = $where ? 'where' : 'on';

            $this->joins[] = $join->$method($first, $operator, $second);

            $this->addBinding($join->getBindings(), 'join');
        }

        return $this;
    }

    /**
     * Add a "join where" clause to the query.
     *
     * @param  string                                     $table
     * @param  string                                     $first
     * @param  string                                     $operator
     * @param  string                                     $second
     * @param  string                                     $type
     * @return \Mellivora\Database\Query\Builder|static
     */
    function joinWhere($table, $first, $operator, $second, $type = 'inner')
    {
        return $this->join($table, $first, $operator, $second, $type, true);
    }

    /**
     * Add a left join to the query.
     *
     * @param  string                                     $table
     * @param  string                                     $first
     * @param  string                                     $operator
     * @param  string                                     $second
     * @return \Mellivora\Database\Query\Builder|static
     */
    function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    /**
     * Add a "join where" clause to the query.
     *
     * @param  string                                     $table
     * @param  string                                     $first
     * @param  string                                     $operator
     * @param  string                                     $second
     * @return \Mellivora\Database\Query\Builder|static
     */
    function leftJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'left');
    }

    /**
     * Add a right join to the query.
     *
     * @param  string                                     $table
     * @param  string                                     $first
     * @param  string                                     $operator
     * @param  string                                     $second
     * @return \Mellivora\Database\Query\Builder|static
     */
    function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    /**
     * Add a "right join where" clause to the query.
     *
     * @param  string                                     $table
     * @param  string                                     $first
     * @param  string                                     $operator
     * @param  string                                     $second
     * @return \Mellivora\Database\Query\Builder|static
     */
    function rightJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'right');
    }

    /**
     * Add a "cross join" clause to the query.
     *
     * @param  string                                     $table
     * @param  string                                     $first
     * @param  string                                     $operator
     * @param  string                                     $second
     * @return \Mellivora\Database\Query\Builder|static
     */
    function crossJoin($table, $first = null, $operator = null, $second = null)
    {
        if ($first) {
            return $this->join($table, $first, $operator, $second, 'cross');
        }

        $this->joins[] = new JoinClause($this, 'cross', $table);

        return $this;
    }

    /**
     * Apply the callback's query changes if the given "value" is true.
     *
     * @param  bool                                $value
     * @param  \Closure                            $callback
     * @param  \Closure                            $default
     * @return \Mellivora\Database\Query\Builder
     */
    function when($value, $callback, $default = null)
    {
        $builder = $this;

        if ($value) {
            $builder = call_user_func($callback, $builder);
        } elseif ($default) {
            $builder = call_user_func($default, $builder);
        }

        return $builder;
    }

    /**
     * Merge an array of where clauses and bindings.
     *
     * @param  array  $wheres
     * @param  array  $bindings
     * @return void
     */
    function mergeWheres($wheres, $bindings)
    {
        $this->wheres = array_merge((array) $this->wheres, (array) $wheres);

        $this->bindings['where'] = array_values(
            array_merge($this->bindings['where'], (array) $bindings)
        );
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure $column
     * @param  string                $operator
     * @param  mixed                 $value
     * @param  string                $boolean
     * @return $this
     */
    function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );

        // If the columns is actually a Closure instance, we will assume the developer
        // wants to begin a nested where statement which is wrapped in parenthesis.
        // We'll add that Closure to the query then return back out immediately.
        if ($column instanceof Closure) {
            return $this->whereNested($column, $boolean);
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        // If the value is a Closure, it means the developer is performing an entire
        // sub-select within the query and we will need to compile the sub-select
        // within the where clause to get the appropriate query record results.
        if ($value instanceof Closure) {
            return $this->whereSub($column, $operator, $value, $boolean);
        }

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator != '=');
        }

        // If the column is making a JSON reference we'll check to see if the value
        // is a boolean. If it is, we'll add the raw boolean string as an actual
        // value to the query to ensure this is properly handled by the query.
        if (Str::contains($column, '->') && is_bool($value)) {
            $value = new Expression($value ? 'true' : 'false');
        }

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $type = 'Basic';

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        if (!$value instanceof Expression) {
            $this->addBinding($value, 'where');
        }

        return $this;
    }

    /**
     * Add an array of where clauses to the query.
     *
     * @param  array   $column
     * @param  string  $boolean
     * @param  string  $method
     * @return $this
     */
    function addArrayOfWheres($column, $boolean, $method = 'where')
    {
        return $this->whereNested(function ($query) use ($column, $method) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->$method($key, '=', $value);
                }
            }
        }, $boolean);
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param  string                      $value
     * @param  string                      $operator
     * @param  bool                        $useDefault
     * @throws \InvalidArgumentException
     * @return array
     */
    function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     *
     * @param  string $operator
     * @param  mixed  $value
     * @return bool
     */
    function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
        !in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * Determine if the given operator is supported.
     *
     * @param  string $operator
     * @return bool
     */
    function invalidOperator($operator)
    {
        return !in_array(strtolower($operator), $this->operators, true) &&
        !in_array(strtolower($operator), $this->grammar->getOperators(), true);
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param  \Closure|string                            $column
     * @param  string                                     $operator
     * @param  mixed                                      $value
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a "where" clause comparing two columns to the query.
     *
     * @param  string|array                               $first
     * @param  string|null                                $operator
     * @param  string|null                                $second
     * @param  string|null                                $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($first)) {
            return $this->addArrayOfWheres($first, $boolean, 'whereColumn');
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($second, $operator) = [$operator, '='];
        }

        // Finally, we will add this where clause into this array of clauses that we
        // are building for the query. All of them will be compiled via a grammar
        // once the query is about to be executed and run against the database.
        $type = 'Column';

        $this->wheres[] = compact(
            'type', 'first', 'operator', 'second', 'boolean'
        );

        return $this;
    }

    /**
     * Add an "or where" clause comparing two columns to the query.
     *
     * @param  string|array                               $first
     * @param  string|null                                $operator
     * @param  string|null                                $second
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param  string  $sql
     * @param  mixed   $bindings
     * @param  string  $boolean
     * @return $this
     */
    function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];

        $this->addBinding((array) $bindings, 'where');

        return $this;
    }

    /**
     * Add a raw or where clause to the query.
     *
     * @param  string                                     $sql
     * @param  array                                      $bindings
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereRaw($sql, array $bindings = [])
    {
        return $this->whereRaw($sql, $bindings, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        // If the value is a query builder instance we will assume the developer wants to
        // look for any values that exists within this given query. So we will add the
        // query accordingly so that this query is properly executed when it is run.
        if ($values instanceof static ) {
            return $this->whereInExistingQuery(
                $column, $values, $boolean, $not
            );
        }

        // If the value of the where in clause is actually a Closure, we will assume that
        // the developer is using a full sub-select for this "in" statement, and will
        // execute those Closures, then we can re-construct the entire sub-selects.
        if ($values instanceof Closure) {
            return $this->whereInSub($column, $values, $boolean, $not);
        }

        // Next, if the value is Arrayable we need to cast it to its raw array form so we
        // have the underlying array value instead of an Arrayable object which is not
        // able to be added as a binding, etc. We will then add to the wheres array.
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        // Finally we'll add a binding for each values unless that value is an expression
        // in which case we will just skip over it since it will be the query as a raw
        // string and not as a parameterized place-holder to be replaced by the PDO.
        foreach ($values as $value) {
            if (!$value instanceof Expression) {
                $this->addBinding($value, 'where');
            }
        }

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param  string                                     $column
     * @param  mixed                                      $values
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string                                     $column
     * @param  mixed                                      $values
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param  string                                     $column
     * @param  mixed                                      $values
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a where in with a sub-select to the query.
     *
     * @param  string   $column
     * @param  \Closure $callback
     * @param  string   $boolean
     * @param  bool     $not
     * @return $this
     */
    function whereInSub($column, Closure $callback, $boolean, $not)
    {
        $type = $not ? 'NotInSub' : 'InSub';

        // To create the exists sub-select, we will actually create a query and call the
        // provided callback with the query so the developer may set any of the query
        // conditions they want for the in clause, then we'll put it in this array.
        call_user_func($callback, $query = $this->newQuery());

        $this->wheres[] = compact('type', 'column', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add an external sub-select to the query.
     *
     * @param  string                                   $column
     * @param  \Mellivora\Database\Query\Builder|static $query
     * @param  string                                   $boolean
     * @param  bool                                     $not
     * @return $this
     */
    function whereInExistingQuery($column, $query, $boolean, $not)
    {
        $type = $not ? 'NotInSub' : 'InSub';

        $this->wheres[] = compact('type', 'column', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    function whereNull($column, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        $this->wheres[] = compact('type', 'column', 'boolean');

        return $this;
    }

    /**
     * Add an "or where null" clause to the query.
     *
     * @param  string                                     $column
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereNull($column)
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereNotNull($column, $boolean = 'and')
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add a where between statement to the query.
     *
     * @param  string  $column
     * @param  array   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        $this->wheres[] = compact('column', 'type', 'boolean', 'not');

        $this->addBinding($values, 'where');

        return $this;
    }

    /**
     * Add an or where between statement to the query.
     *
     * @param  string                                     $column
     * @param  array                                      $values
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
     *
     * @param  string                                     $column
     * @param  array                                      $values
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereNotBetween($column, array $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     *
     * @param  string                                     $column
     * @param  array                                      $values
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereNotBetween($column, array $values)
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param  string                                     $column
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'or');
    }

    /**
     * Add a "where date" statement to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $operator
     * @param  mixed                                      $value
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );

        return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where date" statement to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $operator
     * @param  string                                     $value
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereDate($column, $operator, $value)
    {
        return $this->whereDate($column, $operator, $value, 'or');
    }

    /**
     * Add a "where time" statement to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $operator
     * @param  int                                        $value
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereTime($column, $operator, $value, $boolean = 'and')
    {
        return $this->addDateBasedWhere('Time', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where time" statement to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $operator
     * @param  int                                        $value
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereTime($column, $operator, $value)
    {
        return $this->whereTime($column, $operator, $value, 'or');
    }

    /**
     * Add a "where day" statement to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $operator
     * @param  mixed                                      $value
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );

        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "where month" statement to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $operator
     * @param  mixed                                      $value
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );

        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "where year" statement to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $operator
     * @param  mixed                                      $value
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );

        return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
    }

    /**
     * Add a date based (year, month, day, time) statement to the query.
     *
     * @param  string  $type
     * @param  string  $column
     * @param  string  $operator
     * @param  int     $value
     * @param  string  $boolean
     * @return $this
     */
    function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
    {
        $this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

        $this->addBinding($value, 'where');

        return $this;
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param  \Closure                                   $callback
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereNested(Closure $callback, $boolean = 'and')
    {
        call_user_func($callback, $query = $this->forNestedWhere());

        return $this->addNestedWhereQuery($query, $boolean);
    }

    /**
     * Create a new query instance for nested where condition.
     *
     * @return \Mellivora\Database\Query\Builder
     */
    function forNestedWhere()
    {
        return $this->newQuery()->from($this->from);
    }

    /**
     * Add another query builder as a nested where to the query builder.
     *
     * @param  \Mellivora\Database\Query\Builder|static $query
     * @param  string                                   $boolean
     * @return $this
     */
    function addNestedWhereQuery($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getBindings(), 'where');
        }

        return $this;
    }

    /**
     * Add a full sub-select to the query.
     *
     * @param  string   $column
     * @param  string   $operator
     * @param  \Closure $callback
     * @param  string   $boolean
     * @return $this
     */
    function whereSub($column, $operator, Closure $callback, $boolean)
    {
        $type = 'Sub';

        // Once we have the query instance we can simply execute it so it can add all
        // of the sub-select's conditions to itself, and then we can cache it off
        // in the array of where clauses for the "main" parent query instance.
        call_user_func($callback, $query = $this->newQuery());

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'query', 'boolean'
        );

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add an exists clause to the query.
     *
     * @param  \Closure $callback
     * @param  string   $boolean
     * @param  bool     $not
     * @return $this
     */
    function whereExists(Closure $callback, $boolean = 'and', $not = false)
    {
        $query = $this->newQuery();

        // Similar to the sub-select clause, we will create a new query instance so
        // the developer may cleanly specify the entire exists query and we will
        // compile the whole thing in the grammar and insert it into the SQL.
        call_user_func($callback, $query);

        return $this->addWhereExistsQuery($query, $boolean, $not);
    }

    /**
     * Add an or exists clause to the query.
     *
     * @param  \Closure                                   $callback
     * @param  bool                                       $not
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereExists(Closure $callback, $not = false)
    {
        return $this->whereExists($callback, 'or', $not);
    }

    /**
     * Add a where not exists clause to the query.
     *
     * @param  \Closure                                   $callback
     * @param  string                                     $boolean
     * @return \Mellivora\Database\Query\Builder|static
     */
    function whereNotExists(Closure $callback, $boolean = 'and')
    {
        return $this->whereExists($callback, $boolean, true);
    }

    /**
     * Add a where not exists clause to the query.
     *
     * @param  \Closure                                   $callback
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orWhereNotExists(Closure $callback)
    {
        return $this->orWhereExists($callback, true);
    }

    /**
     * Add an exists clause to the query.
     *
     * @param  \Mellivora\Database\Query\Builder $query
     * @param  string                            $boolean
     * @param  bool                              $not
     * @return $this
     */
    function addWhereExistsQuery(Builder $query, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotExists' : 'Exists';

        $this->wheres[] = compact('type', 'operator', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param  string  $method
     * @param  string  $parameters
     * @return $this
     */
    function dynamicWhere($method, $parameters)
    {
        $finder = substr($method, 5);

        $segments = preg_split(
            '/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE
        );

        // The connector variable will determine which connector will be used for the
        // query condition. We will change it as we come across new boolean values
        // in the dynamic method strings, which could contain a number of these.
        $connector = 'and';

        $index = 0;

        foreach ($segments as $segment) {
            // If the segment is not a boolean connector, we can assume it is a column's name
            // and we will add it to the query as a new constraint as a where clause, then
            // we can keep iterating through the dynamic method string's segments again.
            if ($segment != 'And' && $segment != 'Or') {
                $this->addDynamic($segment, $connector, $parameters, $index);

                $index++;
            }

            // Otherwise, we will store the connector so we know how the next where clause we
            // find in the query should be connected to the previous ones, meaning we will
            // have the proper boolean connector to connect the next where clause found.
            else {
                $connector = $segment;
            }
        }

        return $this;
    }

    /**
     * Add a single dynamic where clause statement to the query.
     *
     * @param  string $segment
     * @param  string $connector
     * @param  array  $parameters
     * @param  int    $index
     * @return void
     */
    function addDynamic($segment, $connector, $parameters, $index)
    {
        // Once we have parsed out the columns and formatted the boolean operators we
        // are ready to add it to this query as a where clause just like any other
        // clause on the query. Then we'll increment the parameter index values.
        $bool = strtolower($connector);

        $this->where(Str::snake($segment), '=', $parameters[$index], $bool);
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param  array   ...$groups
     * @return $this
     */
    function groupBy(...$groups)
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array) $this->groups,
                array_wrap($group)
            );
        }

        return $this;
    }

    /**
     * Add a "having" clause to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  string  $value
     * @param  string  $boolean
     * @return $this
     */
    function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'Basic';

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (!$value instanceof Expression) {
            $this->addBinding($value, 'having');
        }

        return $this;
    }

    /**
     * Add a "or having" clause to the query.
     *
     * @param  string                                     $column
     * @param  string                                     $operator
     * @param  string                                     $value
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orHaving($column, $operator = null, $value = null)
    {
        return $this->having($column, $operator, $value, 'or');
    }

    /**
     * Add a raw having clause to the query.
     *
     * @param  string  $sql
     * @param  array   $bindings
     * @param  string  $boolean
     * @return $this
     */
    function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $type = 'Raw';

        $this->havings[] = compact('type', 'sql', 'boolean');

        $this->addBinding($bindings, 'having');

        return $this;
    }

    /**
     * Add a raw or having clause to the query.
     *
     * @param  string                                     $sql
     * @param  array                                      $bindings
     * @return \Mellivora\Database\Query\Builder|static
     */
    function orHavingRaw($sql, array $bindings = [])
    {
        return $this->havingRaw($sql, $bindings, 'or');
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     */
    function orderBy($column, $direction = 'asc')
    {
        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = [
            'column'    => $column,
            'direction' => strtolower($direction) == 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param  string                                     $column
     * @return \Mellivora\Database\Query\Builder|static
     */
    function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param  string                                     $column
     * @return \Mellivora\Database\Query\Builder|static
     */
    function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Put the query's results in random order.
     *
     * @param  string  $seed
     * @return $this
     */
    function inRandomOrder($seed = '')
    {
        return $this->orderByRaw($this->grammar->compileRandom($seed));
    }

    /**
     * Add a raw "order by" clause to the query.
     *
     * @param  string  $sql
     * @param  array   $bindings
     * @return $this
     */
    function orderByRaw($sql, $bindings = [])
    {
        $type = 'Raw';

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = compact('type', 'sql');

        $this->addBinding($bindings, 'order');

        return $this;
    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param  int                                        $value
     * @return \Mellivora\Database\Query\Builder|static
     */
    function skip($value)
    {
        return $this->offset($value);
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param  int     $value
     * @return $this
     */
    function offset($value)
    {
        $property = $this->unions ? 'unionOffset' : 'offset';

        $this->$property = max(0, $value);

        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param  int                                        $value
     * @return \Mellivora\Database\Query\Builder|static
     */
    function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int     $value
     * @return $this
     */
    function limit($value)
    {
        $property = $this->unions ? 'unionLimit' : 'limit';

        if ($value >= 0) {
            $this->$property = $value;
        }

        return $this;
    }

    /**
     * Set the limit and offset for a given page.
     *
     * @param  int                                        $page
     * @param  int                                        $perPage
     * @return \Mellivora\Database\Query\Builder|static
     */
    function forPage($page, $perPage = 15)
    {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }

    /**
     * Constrain the query to the next "page" of results after a given ID.
     *
     * @param  int                                        $perPage
     * @param  int                                        $lastId
     * @param  string                                     $column
     * @return \Mellivora\Database\Query\Builder|static
     */
    function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
    {
        $this->orders = $this->removeExistingOrdersFor($column);

        return $this->where($column, '>', $lastId)
            ->orderBy($column, 'asc')
            ->take($perPage);
    }

    /**
     * Get an array orders with all orders for an given column removed.
     *
     * @param  string  $column
     * @return array
     */
    function removeExistingOrdersFor($column)
    {
        return Collection::make($this->orders)
            ->reject(function ($order) use ($column) {
                return $order['column'] === $column;
            })->values()->all();
    }

    /**
     * Add a union statement to the query.
     *
     * @param  \Mellivora\Database\Query\Builder|\Closure $query
     * @param  bool                                       $all
     * @return \Mellivora\Database\Query\Builder|static
     */
    function union($query, $all = false)
    {
        if ($query instanceof Closure) {
            call_user_func($query, $query = $this->newQuery());
        }

        $this->unions[] = compact('query', 'all');

        $this->addBinding($query->getBindings(), 'union');

        return $this;
    }

    /**
     * Add a union all statement to the query.
     *
     * @param  \Mellivora\Database\Query\Builder|\Closure $query
     * @return \Mellivora\Database\Query\Builder|static
     */
    function unionAll($query)
    {
        return $this->union($query, true);
    }

    /**
     * Lock the selected rows in the table.
     *
     * @param  string|bool $value
     * @return $this
     */
    function lock($value = true)
    {
        $this->lock = $value;

        if (!is_null($this->lock)) {
            $this->useWritePdo();
        }

        return $this;
    }

    /**
     * Lock the selected rows in the table for updating.
     *
     * @return \Mellivora\Database\Query\Builder
     */
    function lockForUpdate()
    {
        return $this->lock(true);
    }

    /**
     * Share lock the selected rows in the table.
     *
     * @return \Mellivora\Database\Query\Builder
     */
    function sharedLock()
    {
        return $this->lock(false);
    }

    /**
     * Get the SQL representation of the query.
     *
     * @return string
     */
    function toSql()
    {
        return $this->grammar->compileSelect($this);
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param  int            $id
     * @param  array          $columns
     * @return mixed|static
     */
    function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param  string  $column
     * @return mixed
     */
    function value($column)
    {
        $result = (array) $this->first([$column]);

        return count($result) > 0 ? reset($result) : null;
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array                  $columns
     * @return \stdClass|array|null
     */
    function first($columns = ['*'])
    {
        return $this->take(1)->get($columns)->first();
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array                           $columns
     * @return \Mellivora\Support\Collection
     */
    function get($columns = ['*'])
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        $results = $this->processor->processSelect($this, $this->runSelect());

        $this->columns = $original;

        return collect($results);
    }

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    function runSelect()
    {
        return $this->connection->select(
            $this->toSql(), $this->getBindings(), !$this->useWritePdo
        );
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int                                                            $perPage
     * @param  array                                                          $columns
     * @param  string                                                         $pageName
     * @param  int|null                                                       $page
     * @return \Mellivora\Support\Contracts\Pagination\LengthAwarePaginator
     */
    function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = $this->getCountForPagination($columns);

        $results = $total ? $this->forPage($page, $perPage)->get($columns) : collect();

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get a paginator only supporting simple next and previous links.
     *
     * This is more efficient on larger data-sets, etc.
     *
     * @param  int                                                 $perPage
     * @param  array                                               $columns
     * @param  string                                              $pageName
     * @param  int|null                                            $page
     * @return \Mellivora\Support\Contracts\Pagination\Paginator
     */
    function simplePaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $this->skip(($page - 1) * $perPage)->take($perPage + 1);

        return new Paginator($this->get($columns), $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get the count of the total records for the paginator.
     *
     * @param  array $columns
     * @return int
     */
    function getCountForPagination($columns = ['*'])
    {
        $results = $this->runPaginationCountQuery($columns);

        // Once we have run the pagination count query, we will get the resulting count and
        // take into account what type of query it was. When there is a group by we will
        // just return the count of the entire results set since that will be correct.
        if (isset($this->groups)) {
            return count($results);
        } elseif (!isset($results[0])) {
            return 0;
        } elseif (is_object($results[0])) {
            return (int) $results[0]->aggregate;
        } else {
            return (int) array_change_key_case((array) $results[0])['aggregate'];
        }
    }

    /**
     * Run a pagiantion count query.
     *
     * @param  array   $columns
     * @return array
     */
    function runPaginationCountQuery($columns = ['*'])
    {
        return $this->cloneWithout(['columns', 'orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['select', 'order'])
            ->setAggregate('count', $this->withoutSelectAliases($columns))
            ->get()->all();
    }

    /**
     * Remove the column aliases since they will break count queries.
     *
     * @param  array   $columns
     * @return array
     */
    function withoutSelectAliases(array $columns)
    {
        return array_map(function ($column) {
            return is_string($column) && ($aliasPosition = strpos(strtolower($column), ' as ')) !== false
                ? substr($column, 0, $aliasPosition) : $column;
        }, $columns);
    }

    /**
     * Get a generator for the given query.
     *
     * @return \Generator
     */
    function cursor()
    {
        if (is_null($this->columns)) {
            $this->columns = ['*'];
        }

        return $this->connection->cursor(
            $this->toSql(), $this->getBindings(), !$this->useWritePdo
        );
    }

    /**
     * Chunk the results of the query.
     *
     * @param  int      $count
     * @param  callable $callback
     * @return bool
     */
    function chunk($count, callable $callback)
    {
        $this->enforceOrderBy();

        $page = 1;

        do {
            // We'll execute the query for the given page and get the results. If there are
            // no results we can just break and return from here. When there are results
            // we will call the callback with the current chunk of these results here.
            $results = $this->forPage($page, $count)->get();

            $countResults = $results->count();

            if ($countResults == 0) {
                break;
            }

            // On each chunk result set, we will pass them to the callback and then let the
            // developer take care of everything within the callback, which allows us to
            // keep the memory low for spinning through large result sets for working.
            if ($callback($results) === false) {
                return false;
            }

            $page++;
        } while ($countResults == $count);

        return true;
    }

    /**
     * Chunk the results of a query by comparing numeric IDs.
     *
     * @param  int      $count
     * @param  callable $callback
     * @param  string   $column
     * @param  string   $alias
     * @return bool
     */
    function chunkById($count, callable $callback, $column = 'id', $alias = null)
    {
        $alias = $alias ?: $column;

        $lastId = 0;

        do {
            $clone = clone $this;

            // We'll execute the query for the given page and get the results. If there are
            // no results we can just break and return from here. When there are results
            // we will call the callback with the current chunk of these results here.
            $results = $clone->forPageAfterId($count, $lastId, $column)->get();

            $countResults = $results->count();

            if ($countResults == 0) {
                break;
            }

            // On each chunk result set, we will pass them to the callback and then let the
            // developer take care of everything within the callback, which allows us to
            // keep the memory low for spinning through large result sets for working.
            if ($callback($results) === false) {
                return false;
            }

            $lastId = $results->last()->{$alias};
        } while ($countResults == $count);

        return true;
    }

    /**
     * Throw an exception if the query doesn't have an orderBy clause.
     *
     * @throws \RuntimeException
     * @return void
     */
    function enforceOrderBy()
    {
        if (empty($this->orders) && empty($this->unionOrders)) {
            throw new RuntimeException('You must specify an orderBy clause when using this function.');
        }
    }

    /**
     * Execute a callback over each item while chunking.
     *
     * @param  callable $callback
     * @param  int      $count
     * @return bool
     */
    function each(callable $callback, $count = 1000)
    {
        return $this->chunk($count, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if ($callback($value, $key) === false) {
                    return false;
                }
            }
        });
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param  string                          $column
     * @param  string|null                     $key
     * @return \Mellivora\Support\Collection
     */
    function pluck($column, $key = null)
    {
        $results = $this->get(is_null($key) ? [$column] : [$column, $key]);

        // If the columns are qualified with a table or have an alias, we cannot use
        // those directly in the "pluck" operations since the results from the DB
        // are only keyed by the column itself. We'll strip the table out here.
        return $results->pluck(
            $this->stripTableForPluck($column),
            $this->stripTableForPluck($key)
        );
    }

    /**
     * Strip off the table name or alias from a column identifier.
     *
     * @param  string        $column
     * @return string|null
     */
    function stripTableForPluck($column)
    {
        return is_null($column) ? $column : last(preg_split('~\.| ~', $column));
    }

    /**
     * Concatenate values of a given column as a string.
     *
     * @param  string   $column
     * @param  string   $glue
     * @return string
     */
    function implode($column, $glue = '')
    {
        return $this->pluck($column)->implode($glue);
    }

    /**
     * Determine if any rows exist for the current query.
     *
     * @return bool
     */
    function exists()
    {
        $results = $this->connection->select(
            $this->grammar->compileExists($this), $this->getBindings(), !$this->useWritePdo
        );

        // If the results has rows, we will get the row and see if the exists column is a
        // boolean true. If there is no results for this query we will return false as
        // there are no rows for this query at all and we can return that info here.
        if (isset($results[0])) {
            $results = (array) $results[0];

            return (bool) $results['exists'];
        }

        return false;
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  string $columns
     * @return int
     */
    function count($columns = '*')
    {
        return (int) $this->aggregate(__FUNCTION__, array_wrap($columns));
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    function min($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    function max($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    function sum($column)
    {
        $result = $this->aggregate(__FUNCTION__, [$column]);

        return $result ?: 0;
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    function avg($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Alias for the "avg" method.
     *
     * @param  string  $column
     * @return mixed
     */
    function average($column)
    {
        return $this->avg($column);
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param  string  $function
     * @param  array   $columns
     * @return mixed
     */
    function aggregate($function, $columns = ['*'])
    {
        $results = $this->cloneWithout(['columns'])
            ->cloneWithoutBindings(['select'])
            ->setAggregate($function, $columns)
            ->get($columns);

        if (!$results->isEmpty()) {
            return array_change_key_case((array) $results[0])['aggregate'];
        }
    }

    /**
     * Execute a numeric aggregate function on the database.
     *
     * @param  string      $function
     * @param  array       $columns
     * @return float|int
     */
    function numericAggregate($function, $columns = ['*'])
    {
        $result = $this->aggregate($function, $columns);

        // If there is no result, we can obviously just return 0 here. Next, we will check
        // if the result is an integer or float. If it is already one of these two data
        // types we can just return the result as-is, otherwise we will convert this.
        if (!$result) {
            return 0;
        }

        if (is_int($result) || is_float($result)) {
            return $result;
        }

        // If the result doesn't contain a decimal place, we will assume it is an int then
        // cast it to one. When it does we will cast it to a float since it needs to be
        // cast to the expected data type for the developers out of pure convenience.
        return strpos((string) $result, '.') === false
            ? (int) $result : (float) $result;
    }

    /**
     * Set the aggregate property without running the query.
     *
     * @param  string  $function
     * @param  array   $columns
     * @return $this
     */
    function setAggregate($function, $columns)
    {
        $this->aggregate = compact('function', 'columns');

        return $this;
    }

    /**
     * Insert a new record into the database.
     *
     * @param  array  $values
     * @return bool
     */
    function insert(array $values)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building these
        // inserts statements by verifying these elements are actually an array.
        if (empty($values)) {
            return true;
        }

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        // Here, we will sort the insert keys for every record so that each insert is
        // in the same order for the record. We need to make sure this is the case
        // so there are not any errors or problems when inserting these records.
        else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        // Finally, we will run this query against the database connection and return
        // the results. We will need to also flatten these bindings before running
        // the query so they are all in one huge, flattened array for execution.
        return $this->connection->insert(
            $this->grammar->compileInsert($this, $values),
            $this->cleanBindings(Arr::flatten($values, 1))
        );
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array  $values
     * @param  string $sequence
     * @return int
     */
    function insertGetId(array $values, $sequence = null)
    {
        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

        $values = $this->cleanBindings($values);

        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }

    /**
     * Update a record in the database.
     *
     * @param  array $values
     * @return int
     */
    function update(array $values)
    {
        $sql = $this->grammar->compileUpdate($this, $values);

        return $this->connection->update($sql, $this->cleanBindings(
            $this->grammar->prepareBindingsForUpdate($this->bindings, $values)
        ));
    }

    /**
     * Insert or update a record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return bool
     */
    function updateOrInsert(array $attributes, array $values = [])
    {
        if (!$this->where($attributes)->exists()) {
            return $this->insert(array_merge($attributes, $values));
        }

        return (bool) $this->take(1)->update($values);
    }

    /**
     * Increment a column's value by a given amount.
     *
     * @param  string $column
     * @param  int    $amount
     * @param  array  $extra
     * @return int
     */
    function increment($column, $amount = 1, array $extra = [])
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Non-numeric value passed to increment method.');
        }

        $wrapped = $this->grammar->wrap($column);

        $columns = array_merge([$column => $this->raw("$wrapped + $amount")], $extra);

        return $this->update($columns);
    }

    /**
     * Decrement a column's value by a given amount.
     *
     * @param  string $column
     * @param  int    $amount
     * @param  array  $extra
     * @return int
     */
    function decrement($column, $amount = 1, array $extra = [])
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Non-numeric value passed to decrement method.');
        }

        $wrapped = $this->grammar->wrap($column);

        $columns = array_merge([$column => $this->raw("$wrapped - $amount")], $extra);

        return $this->update($columns);
    }

    /**
     * Delete a record from the database.
     *
     * @param  mixed $id
     * @return int
     */
    function delete($id = null)
    {
        // If an ID is passed to the method, we will set the where clause to check the
        // ID to let developers to simply and quickly remove a single row from this
        // database without manually specifying the "where" clauses on the query.
        if (!is_null($id)) {
            $this->where($this->from . '.id', '=', $id);
        }

        return $this->connection->delete(
            $this->grammar->compileDelete($this), $this->getBindings()
        );
    }

    /**
     * Run a truncate statement on the table.
     *
     * @return void
     */
    function truncate()
    {
        foreach ($this->grammar->compileTruncate($this) as $sql => $bindings) {
            $this->connection->statement($sql, $bindings);
        }
    }

    /**
     * Get a new instance of the query builder.
     *
     * @return \Mellivora\Database\Query\Builder
     */
    function newQuery()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Create a raw database expression.
     *
     * @param  mixed                                  $value
     * @return \Mellivora\Database\Query\Expression
     */
    function raw($value)
    {
        return $this->connection->raw($value);
    }

    /**
     * Get the current query value bindings in a flattened array.
     *
     * @return array
     */
    function getBindings()
    {
        return Arr::flatten($this->bindings);
    }

    /**
     * Get the raw array of bindings.
     *
     * @return array
     */
    function getRawBindings()
    {
        return $this->bindings;
    }

    /**
     * Set the bindings on the query builder.
     *
     * @param  array                       $bindings
     * @param  string                      $type
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setBindings(array $bindings, $type = 'where')
    {
        if (!array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        $this->bindings[$type] = $bindings;

        return $this;
    }

    /**
     * Add a binding to the query.
     *
     * @param  mixed                       $value
     * @param  string                      $type
     * @throws \InvalidArgumentException
     * @return $this
     */
    function addBinding($value, $type = 'where')
    {
        if (!array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
        } else {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }

    /**
     * Merge an array of bindings into our bindings.
     *
     * @param  \Mellivora\Database\Query\Builder $query
     * @return $this
     */
    function mergeBindings(Builder $query)
    {
        $this->bindings = array_merge_recursive($this->bindings, $query->bindings);

        return $this;
    }

    /**
     * Remove all of the expressions from a list of bindings.
     *
     * @param  array   $bindings
     * @return array
     */
    function cleanBindings(array $bindings)
    {
        return array_values(array_filter($bindings, function ($binding) {
            return !$binding instanceof Expression;
        }));
    }

    /**
     * Get the database connection instance.
     *
     * @return \Mellivora\Database\ConnectionInterface
     */
    function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the database query processor instance.
     *
     * @return \Mellivora\Database\Query\Processors\Processor
     */
    function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Get the query grammar instance.
     *
     * @return \Mellivora\Database\Query\Grammars\Grammar
     */
    function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * Use the write pdo for query.
     *
     * @return $this
     */
    function useWritePdo()
    {
        $this->useWritePdo = true;

        return $this;
    }

    /**
     * Clone the query without the given properties.
     *
     * @param  array    $except
     * @return static
     */
    function cloneWithout(array $except)
    {
        return tap(clone $this, function ($clone) use ($except) {
            foreach ($except as $property) {
                $clone->{$property} = null;
            }
        });
    }

    /**
     * Clone the query without the given bindings.
     *
     * @param  array    $except
     * @return static
     */
    function cloneWithoutBindings(array $except)
    {
        return tap(clone $this, function ($clone) use ($except) {
            foreach ($except as $type) {
                $clone->bindings[$type] = [];
            }
        });
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string                    $method
     * @param  array                     $parameters
     * @throws \BadMethodCallException
     * @return mixed
     */
    function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (Str::startsWith($method, 'where')) {
            return $this->dynamicWhere($method, $parameters);
        }

        $className = static::class;

        throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }
}
