// Initialize pagination plugin
function processData(data) {
    let resultDiv = document.getElementById('resultDiv');
    resultDiv.innerHTML = ''; // Clear previous content

    // Iterate over each key in the data object
    Object.keys(data).forEach(key => {
        const keyData = data[key];

        // Create a title for each section
        const h2 = document.createElement('h2');
        h2.textContent = key.charAt(0).toUpperCase() + key.slice(1); // use the key as title
        resultDiv.appendChild(h2);

        // Create table
        const table = document.createElement('table');
        table.className = 'table table-striped';
        resultDiv.appendChild(table);

        const thead = table.createTHead();
        thead.className = 'thead-dark';
        const headerRow = thead.insertRow();

        // Check if there is data to populate the table
        if (keyData && keyData.length > 0) {
            // Populate the table header
            Object.keys(keyData[0]).forEach(subKey => {
                const th = document.createElement('th');
                th.textContent = subKey; // Assign column names to table headers
                headerRow.appendChild(th);
            });

            const tbodyId = `${key}-tbody`;
            const tbody = table.createTBody();
            tbody.id = tbodyId;
            resultDiv.appendChild(table);

            const paginationContainerId = `${key}-pagination-container`;
            const paginationParent = document.createElement('div');
            paginationParent.id = paginationContainerId;
            resultDiv.appendChild(paginationParent);

            // Initialize pagination plugin
            $(`#${paginationContainerId}`).pagination({
                dataSource: keyData,
                pageSize: 8,
                className: 'paginationjs-theme-blue',
                callback: function(data, pagination) {
                    // Empty tbody
                    const tbodyEl = document.getElementById(tbodyId);
                    tbodyEl.innerHTML = '';

                    // Populate the table rows
                    data.forEach(item => {
                        let row = tbodyEl.insertRow();
                        Object.values(item).forEach(value => {
                            let cell = row.insertCell();
                            let text = document.createTextNode(value);  // Create Text node
                            cell.appendChild(text);  // Append the text node to the cell
                        });
                    });

                    // Pagination button styles
                    $('.paginationjs-pages ul li a').each(function() {
                        $(this).addClass('btn'); // Apply base Bootstrap button classes.
                        $(this).css('background-color', '#343a40'); // Default color for unclicked buttons
                        $(this).css('color', 'white'); // Default text color for buttons

                        if ($(this).parent().hasClass('active')) { // If the button has been clicked
                            $(this).css('background-color', '#dee2e6');
                            $(this).css('color', 'black'); // Default text color for buttons
                        }
                    });
                }
            });

        } else {
            // If no data, show a message in place of the table
            const noDataMessage = document.createElement('div');
            noDataMessage.className = 'no-data-message';
            noDataMessage.textContent = 'No data available';
            resultDiv.appendChild(noDataMessage);
        }
    });
};

