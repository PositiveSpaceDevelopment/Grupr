# Grupr

##To create the database using the "createDatabase.sql" file, do the following:

1 Upload the sql file FROM the local machine to the server using the following command:

```
scp /path/createDatabase.sql <server address>
```

2 From your server, run the following command to execute the sql scrippity-script file
```
\. createDatabase.sql
```

3 Your database has now been created.
