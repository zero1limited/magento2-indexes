# magento2-indexes
Add index commands and logging to the index process.  
All the commands added are normally ran by the cron (group index), this just allows them to be ran on demmand.  
This shouldn't be ran, whilst cron is running, as 2 process running the same thing may cause issues.


## Commands
- `indexer:clean-changelogs` - clear down change logs (runs after `indexer:update-mview`)
- `indexer:reindex-all-invalid` - trigger a full reindex of all invalid indexes (only applicable when indexes are in 'realtime' mode)
- `indexer:update-mview` - apply all change logs

## Logging
By default this module will echo output when the indexers run. it will also log the info (level debug).
