// Import the mysql module
const createConnection = require("mysql");

// Create a connection to the database
const connection = createConnection({
	host: "localhost", // Replace with your MySQL host
	user: "root", // Replace with your MySQL username
	password: "", // Replace with your MySQL password
	database: "dbname", // Replace with your MySQL database name
});

// Connect to the database
connection.connect((err) => {
	if (err) {
		console.error("Error connecting to database:", err);
		return;
	}
	console.log("Connected to MySQL database");

	// Perform queries or other database operations here
	// For example:
	connection.query("SELECT * FROM your_table", (err, rows) => {
		if (err) {
			console.error("Error executing query:", err);
			return;
		}
		console.log("Data received from MySQL database:");
		console.log(rows);
	});
});

// Close the connection when done
connection.end((err) => {
	if (err) {
		console.error("Error closing database connection:", err);
		return;
	}
	console.log("Connection to MySQL database closed");
});
