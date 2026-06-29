class Loan {
  final int    id;
  final int    accountId;
  final String debtorName;
  final double amount;
  final String loanDate;
  final String? dueDate;
  final String  description;
  final String? accountName;

  const Loan({
    required this.id,
    required this.accountId,
    required this.debtorName,
    required this.amount,
    required this.loanDate,
    this.dueDate,
    required this.description,
    this.accountName,
  });

  factory Loan.fromJson(Map<String, dynamic> j) => Loan(
        id:          (j['id'] as num).toInt(),
        accountId:   (j['account_id'] as num).toInt(),
        debtorName:  j['debtor_name'] as String,
        amount:      double.tryParse(j['amount'].toString()) ?? 0,
        loanDate:    j['loan_date'] as String,
        dueDate:     j['due_date'] as String?,
        description: j['description'] as String? ?? '',
        accountName: j['account_name'] as String?,
      );
}

class LoanPayment {
  final int    id;
  final int    loanId;
  final int    accountId;
  final double amount;
  final String paymentDate;
  final String? accountName;

  const LoanPayment({
    required this.id,
    required this.loanId,
    required this.accountId,
    required this.amount,
    required this.paymentDate,
    this.accountName,
  });

  factory LoanPayment.fromJson(Map<String, dynamic> j) => LoanPayment(
        id:          (j['id'] as num).toInt(),
        loanId:      (j['loan_id'] as num).toInt(),
        accountId:   (j['account_id'] as num).toInt(),
        amount:      double.tryParse(j['amount'].toString()) ?? 0,
        paymentDate: j['payment_date'] as String,
        accountName: j['account_name'] as String?,
      );
}
