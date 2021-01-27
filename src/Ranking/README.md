
Since the ranking algorithm is a political choice, and even though _Majority Judgment_ is in my opinion the most elegant ranking system, it makes sense to have multiple rankings algorithms available.

When querying for results of a poll, you may specify a ranking algorithm and some options for it, if any.


## Define your own Ranking

1. Create a PHP class in this directory, eg: `UsualJudgmentRanking`.
2. Implement the `RankingInterface`.
3. That's it!  You don't have to register it anywhere.

Your PHP class will be a Service and therefore can inject any other Service,
for example to access the database or even send emails. (best not, though)


## Available Rankings

- `Majority Judgment` (default)
- …
