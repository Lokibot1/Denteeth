<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tables with Next and Previous</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .table-container {
            display: none;
        }

        .table-container.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Tables with Next and Previous</h2>

        <!-- Table 1 -->
        <div id="table1" class="table-container active">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Column 1</th>
                        <th>Column 2</th>
                        <th>Column 3</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Data 1</td>
                        <td>Data 2</td>
                        <td>Data 3</td>
                    </tr>
                    <tr>
                        <td>Data 4</td>
                        <td>Data 5</td>
                        <td>Data 6</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table 2 -->
        <div id="table2" class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Column A</th>
                        <th>Column B</th>
                        <th>Column C</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Data A</td>
                        <td>Data B</td>
                        <td>Data C</td>
                    </tr>
                    <tr>
                        <td>Data X</td>
                        <td>Data Y</td>
                        <td>Data Z</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Navigation Buttons -->
        <div class="text-center mt-4">
            <button class="btn btn-primary" id="prevBtn" disabled>Previous</button>
            <button class="btn btn-primary" id="nextBtn">Next</button>
        </div>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <!-- JavaScript for Table Navigation -->
    <script>
        let currentTable = 1;
        const totalTables = 2;

        document.getElementById('nextBtn').addEventListener('click', function () {
            if (currentTable < totalTables) {
                currentTable++;
                updateTables();
            }
        });

        document.getElementById('prevBtn').addEventListener('click', function () {
            if (currentTable > 1) {
                currentTable--;
                updateTables();
            }
        });

        function updateTables() {
            for (let i = 1; i <= totalTables; i++) {
                document.getElementById('table' + i).classList.remove('active');
            }
            document.getElementById('table' + currentTable).classList.add('active');

            document.getElementById('prevBtn').disabled = currentTable === 1;
            document.getElementById('nextBtn').disabled = currentTable === totalTables;
        }
    </script>
</body>

</html>