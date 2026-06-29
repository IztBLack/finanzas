class AppTransaction {
  final int    id;
  final int    accountId;
  final int    categoryId;
  final double amount;
  final String type; // income | expense
  final String description;
  final String transactionDate;
  final String? accountName;
  final String? categoryName;

  const AppTransaction({
    required this.id,
    required this.accountId,
    required this.categoryId,
    required this.amount,
    required this.type,
    required this.description,
    required this.transactionDate,
    this.accountName,
    this.categoryName,
  });

  factory AppTransaction.fromJson(Map<String, dynamic> j) => AppTransaction(
        id:              (j['id'] as num).toInt(),
        accountId:       (j['account_id'] as num).toInt(),
        categoryId:      (j['category_id'] as num).toInt(),
        amount:          double.tryParse(j['amount'].toString()) ?? 0,
        type:            j['type'] as String,
        description:     j['description'] as String? ?? '',
        transactionDate: j['transaction_date'] as String,
        accountName:     j['account_name'] as String?,
        categoryName:    j['category_name'] as String?,
      );

  bool get isIncome => type == 'income';
}
