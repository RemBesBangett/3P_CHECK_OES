<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: /3P_CHECK_OES/logout');
    exit();
} 

date_default_timezone_set('Asia/Jakarta');

include '../../../MODEL/HISTORY/3P_HISTORY_HANDLER.php';
include '../TEMPLATE/3P_Header.php';
$baseUrl = '/3P_CHECK_OES/';

// Ambil data history
$historyResult = getAllHistory();

// Inisialisasi variabel histories
$histories = [];

// Periksa apakah query berhasil
if ($historyResult['success']) {
    $histories = $historyResult['data']; // Ambil data history
    $totalCount = $historyResult['count']; // Jumlah data
} else {
    // Tampilkan pesan error
    echo "Gagal mengambil data: " . $historyResult['message'];
    exit;
}

$searchColumns = array('NO_SIL', 'PART_NUMBER', 'CUSTOMER_LABEL', 'KANBAN_CONTENT', 'TOTAL_KANBAN', 'TOTAL_LABEL', 'LABEL_CONTENT', 'QTY_LABEL', 'QTY_KANBAN', 'CUSTOMER', 'ITEM_VENDOR', 'PO_NUMBER', 'PREPARE_DATE', 'PREPARE_TIME', 'DELIVERY_DATE', 'STATUS', 'DATA_ID', 'DELIVERY_VANNING', 'KANBAN_ID', 'MANIFEST', 'KANBAN_ITEM', 'USER_ENTRY');

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

// Filter berdasarkan tanggal jika ada
if ($startDate || $endDate) {
    $histories = array_filter($histories, function ($item) use ($startDate, $endDate) {
        $dateTime = DateTime::createFromFormat('d/m/Y', $item['PREPARE_DATE']);
        if (!$dateTime) return false; // Jika format tidak valid

        $timestamp = $dateTime->getTimestamp();
        if ($startDate) {
            $startTimestamp = DateTime::createFromFormat('d/m/Y', $startDate)->getTimestamp();
            if ($timestamp < $startTimestamp) {
                return false;
            }
        }
        if ($endDate) {
            $endTimestamp = DateTime::createFromFormat('d/m/Y', $endDate)->getTimestamp();
            if ($timestamp > $endTimestamp) {
                return false;
            }
        }
        return true;
    });
}

// Filter berdasarkan search query jika ada
if ($searchQuery) {
    $histories = array_filter($histories, function ($item) use ($searchQuery, $searchColumns) {
        foreach ($searchColumns as $column) {
            if (stripos($item[$column], $searchQuery) !== false) {
                return true;
            }
        }
        return false;
    });
}

// Urutkan data berdasarkan PREPARE_TIME
usort($histories, function ($a, $b) {
    return $b['PREPARE_TIME'] <=> $a['PREPARE_TIME'];
});

$dataPerPage = 20;
$totalData = count($histories);
$totalPages = ceil($totalData / $dataPerPage);

// Tentukan halaman saat ini
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));

// Ambil data untuk halaman saat ini
$startIndex = ($currentPage - 1) * $dataPerPage;
$histories = array_slice($histories, $startIndex, $dataPerPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packaging Line History</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/css/bootstrap.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-3.7.1.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/bootstrap-5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $baseUrl; ?>ASSET/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>ASSET/jquery-ui-1.14.0/jquery-ui.min.css">
    <script src="<?php echo $baseUrl; ?>ASSET/jquery-ui-1.14.0/jquery-ui.min.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            font-size: 12px;
        }

        .container-fluid {
            padding: 1rem;
            max-width: 100%;
            overflow-x: hidden;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #4a90e2;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 0.75rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .search-inputs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .search-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .table-container {
            position: relative;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .table thead {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
        }

        .tooltip-inner {
            max-width: 300px;
            text-align: left;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .table tbody tr {
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Report Pre Delivery Check All</h4>
            </div>
            <div class="card-body p-0">
                <form method="GET" action="" id="searchForm" class="d-flex flex-grow-1 me-3">
                    <input type="text" name="search" id="searchInput" placeholder="Search Data" class="form-control me-2" value="<?= htmlspecialchars($searchQuery) ?>">
                    <input type="text" name="start_date" id="startDate" placeholder="Start Date" class="form-control me-2" value="<?= htmlspecialchars($startDate) ?>" readonly>
                    <input type="text" name="end_date" id="endDate" placeholder="End Date" class="form-control me-2" value="<?= htmlspecialchars($endDate) ?>" readonly>
                    <button type="submit" class="btn btn-custom">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" id="clearSearch" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-responsive-stack">
                        <thead>
                            <tr>
                                <th class="resizable-column" style="width: 10px;">No</th>
                                <th style="width: 1%">No SIL</th>
                                <th style="width: 5%">Part Number</th>
                                <th style="width: 5%">Customer Label</th>
                                <th style="width: 1%">Kanban Content</th>
                                <th style="width: 5%">Total Kanban</th>
                                <th style="width: 5%">Total Label</th>
                                <th style="width: 1%">Label Content</th>
                                <th style="width: 5%">QTY Label</th>
                                <th style="width: 5%">QTY Kanban</th>
                                <th style="width: 1%">Customer</th>
                                <th style="width: 5%">Item_Vendor</th>
                                <th style="width: 5%">PO Number</th>
                                <th style="width: 5%">Prepare Date</th>
                                <th style="width: 5%">Prepare time</th>
                                <th style="width: 5%">Delivery Date</th>
                                <th style="width: 5%">Status</th>
                                <th style="width: 5%">Data ID</th>
                                <th style="width: 5%">Delivery Vanning</th>
                                <th style="width: 5%">Kanban Id</th>
                                <th style="width: 5%">Manifest</th>
                                <th style="width: 5%">Kanban Item</th>
                                <th style="width: 5%">User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($histories)): ?>
                                <tr>
                                    <td colspan="22" class="text-center">Tidak ada data history</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($histories as $index => $history): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($history['NO_SIL'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['PART_NUMBER'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['CUSTOMER_LABEL'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['KANBAN_CONTENT'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['TOTAL_KANBAN'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['TOTAL_LABEL'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['LABEL_CONTENT'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['QTY_LABEL'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['QTY_KANBAN'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['CUSTOMER'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['ITEM_VENDOR'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['PO_NUMBER'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['PREPARE_DATE'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['PREPARE_TIME'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['DELIVERY_DATE'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['STATUS'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['DATA_ID'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['DELIVERY_VANNING'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['KANBAN_ID'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['MANIFEST'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['KANBAN_ITEM'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($history['USER_ENTRY'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>

                        <!-- Tambahkan informasi jumlah data -->
                        <tfoot>
                            <tr>
                                <td colspan="22" class="text-center">
                                    Total Records: <?= $totalCount ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1) : ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($searchQuery) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <?php if ($i == $currentPage || $i == $currentPage - 1 || $i == $currentPage + 1 || $i == 1 || $i == $totalPages) : ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($searchQuery) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"><?= $i ?></a>
                    </li>
                <?php elseif ($i == $currentPage - 2 || $i == $currentPage + 2) : ?>
                    <li class="page-item">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($currentPage < $totalPages) : ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($searchQuery) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <script>
        $(document).ready(function() {
            function handleResponsiveTable() {
                if ($(window).width() < 768) {
                    $(".table-responsive-stack").each(function(i) {
                        $(this).find("th").each(function(i) {
                            $("#" + $(this).attr('id')).html($(this).html());
                        });
                    });

                    $(".table-responsive-stack td").each(function(i) {
                        var id = $(this).attr("data-label");
                        $(this).html('<span class="table-responsive-stack-thead">' + id + '</span> ' + $(this).html());
                    });
                } else {
                    $(".table-responsive-stack td").each(function(i) {
                        var id = $(this).attr("data-label");
                        $(this).find(".table-responsive-stack-thead").remove();
                    });
                }
            }

            handleResponsiveTable();

            $(window).resize(function() {
                handleResponsiveTable();
            });

            $('#startDate').datepicker({
                dateFormat: 'dd/mm/yy',
                onSelect: function(dateText, inst) {
                    $(this).val(dateText);
                }
            });
            $('#endDate').datepicker({
                dateFormat: 'dd/mm/yy',
                onSelect: function(dateText, inst) {
                    $(this).val(dateText);
                }
            });

            $('#searchInput').on('keypress', function(e) {
                if (e.which == 13) {
                    $('#searchForm').submit();
                }
            });

            $('#clearSearch').on('click', function() {
                // Clear the search input
                $('#searchInput').val('');
                $('#startDate').val('');
                $('#endDate').val('');
                // Redirect to the same page without search query
                window.location.href = '?page=1'; // or any specific page you want to redirect to
            });

            $('#searchForm').on('submit', function() {
                // Hapus debounce
                clearTimeout(debounceTimer);
            });
        });
    </script>
</body>

</html>

<?php
include '../TEMPLATE/3P_Footer.php';
?>