## Grupr

#To create the database using the "createDatabase.sql" file, do the following:

1. Upload the sql file FROM the local machine to the server using the following command:

<code>
scp /pathToFile/createDatabase.sql server_address_here
</code>

2. From your server, run the following command to execute the sql scrippity-script file

<code>
\. createDatabase.sql
</code>

3. Your database has now been created.
