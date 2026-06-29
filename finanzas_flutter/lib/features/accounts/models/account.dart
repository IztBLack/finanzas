class Account {
  final int    id;
  final String name;
  final String type; // debit | credit
  final double initialBalance;
  final double? creditLimit;
  final int?   cutoffDate;
  final int?   paymentDate;

  const Account({
    required this.id,
    required this.name,
    required this.type,
    required this.initialBalance,
    this.creditLimit,
    this.cutoffDate,
    this.paymentDate,
  });

  factory Account.fromJson(Map<String, dynamic> j) => Account(
        id:             (j['id'] as num).toInt(),
        name:           j['name'] as String,
        type:           j['type'] as String? ?? 'debit',
        initialBalance: double.tryParse(j['initial_balance'].toString()) ?? 0,
        creditLimit:    j['credit_limit'] != null
            ? double.tryParse(j['credit_limit'].toString())
            : null,
        cutoffDate:     j['cutoff_date'] != null
            ? int.tryParse(j['cutoff_date'].toString())
            : null,
        paymentDate:    j['payment_date'] != null
            ? int.tryParse(j['payment_date'].toString())
            : null,
      );

  Map<String, dynamic> toJson() => {
        'name':            name,
        'type':            type,
        'initial_balance': initialBalance,
        if (creditLimit != null)  'credit_limit':  creditLimit,
        if (cutoffDate  != null)  'cutoff_date':   cutoffDate,
        if (paymentDate != null)  'payment_date':  paymentDate,
      };

  bool get isCredit => type == 'credit';
}
