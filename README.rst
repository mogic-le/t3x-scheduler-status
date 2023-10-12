TYPO3 scheduler status API
==========================

This TYPO3 extension provides a simple JSON API route that
returns the `scheduler`__ status:

- Overview status field if everything is fine
- How many tasks failed, are late, disabled and running
- Detailled information about each task


__ https://docs.typo3.org/c/typo3/cms-scheduler/11.5/en-us/Index.html


Setup
-----
1. Install this extension::

     $ composer require mogic/t3x-scheduler-status
2. Include the TypoScript template "Scheduler status" into your root
   TypoScript template
3. Configure the API key in the TypoScript constants
4. Fetch the scheduler status::

     http://typo3.example.org/?type=9251355215&token=mysecret


TYPO3
-----
TYPO3 10 and 11 are supported


API response
------------
Example response::

  {
    "status": "ok",
    "errored": 0,
    "late": 0,
    "running": 0,
    "longrunning": 0,
    "disabled": 0,
    "tasks": [
      {
        "id": 2,
        "name": "IP-Adressen in Datenbanktabellen anonymisieren",
        "description": "",
        "disabled": false,
        "group": "admin",
        "groupid": 1,
        "late": false,
        "running": false,
        "last": null,
        "lasterror": null,
        "lastsuccess": false,
        "next": "2023-04-29T08:31:22+02:00",
        "next_seconds": 600
      }
    ]
  }

Field explanation:

``status``
  Possible values:

  - ``error`` when one of the tasks had an error in their last run
  - ``late`` when one task was not started as planned
  - ``ok`` when all is fine

``running``
  Number of tasks that are currently running.
``longrunning``
  Number of tasks that are still running and should have been started again already.
``next_seconds``
  Number of seconds when the task will be running in.
  Negative if it should have started already but could not because it's still running.
