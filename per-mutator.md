# Effects per Mutator

| Mutator                         | Mutations | Killed | Escaped | Errors | Syntax Errors | Timed Out | Skipped | Ignored | MSI (%s) | Covered MSI (%s) |
| ------------------------------- | --------- | ------ | ------- | ------ | ------------- | --------- | ------- | ------- | -------- | ---------------- |
| ArrayItem                       |        19 |     13 |       6 |      0 |             0 |         0 |       0 |       0 |    68.42 |            68.42 |
| ArrayItemRemoval                |        31 |     14 |      16 |      0 |             0 |         0 |       0 |       0 |    45.16 |            46.67 |
| CastInt                         |         3 |      0 |       3 |      0 |             0 |         0 |       0 |       0 |     0.00 |             0.00 |
| Concat                          |         7 |      3 |       4 |      0 |             0 |         0 |       0 |       0 |    42.86 |            42.86 |
| ConcatOperandRemoval            |        14 |      6 |       8 |      0 |             0 |         0 |       0 |       0 |    42.86 |            42.86 |
| DecrementInteger                |        19 |      6 |      13 |      0 |             0 |         0 |       0 |       0 |    31.58 |            31.58 |
| FalseValue                      |         3 |      3 |       0 |      0 |             0 |         0 |       0 |       0 |   100.00 |           100.00 |
| Finally_                        |         1 |      0 |       1 |      0 |             0 |         0 |       0 |       0 |     0.00 |             0.00 |
| Identical                       |        10 |      9 |       1 |      0 |             0 |         0 |       0 |       0 |    90.00 |            90.00 |
| IncrementInteger                |        19 |      7 |      12 |      0 |             0 |         0 |       0 |       0 |    36.84 |            36.84 |
| InstanceOf_                     |         2 |      1 |       1 |      0 |             0 |         0 |       0 |       0 |    50.00 |            50.00 |
| LogicalAnd                      |         2 |      2 |       0 |      0 |             0 |         0 |       0 |       0 |   100.00 |           100.00 |
| LogicalAndAllSubExprNegation    |         1 |      1 |       0 |      0 |             0 |         0 |       0 |       0 |   100.00 |           100.00 |
| LogicalAndNegation              |         1 |      0 |       1 |      0 |             0 |         0 |       0 |       0 |     0.00 |             0.00 |
| LogicalAndSingleSubExprNegation |         3 |      3 |       0 |      0 |             0 |         0 |       0 |       0 |   100.00 |           100.00 |
| MethodCallRemoval               |       106 |     45 |      26 |      0 |             0 |         0 |       0 |      35 |    63.38 |            63.38 |
| NewObject                       |         1 |      1 |       0 |      0 |             0 |         0 |       0 |       0 |   100.00 |           100.00 |
| NotIdentical                    |         1 |      1 |       0 |      0 |             0 |         0 |       0 |       0 |   100.00 |           100.00 |
| Throw_                          |         1 |      0 |       0 |      0 |             0 |         0 |       0 |       0 |     0.00 |             0.00 |
| TrueValue                       |        10 |      5 |       5 |      0 |             0 |         0 |       0 |       0 |    50.00 |            50.00 |
| UnwrapArrayMerge                |         2 |      2 |       0 |      0 |             0 |         0 |       0 |       0 |   100.00 |           100.00 |
| UnwrapFinally                   |         1 |      0 |       1 |      0 |             0 |         0 |       0 |       0 |     0.00 |             0.00 |
