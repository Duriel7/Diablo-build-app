class User {
  final String nickname;
  final String city;
  final String email;
  final String password;
  final double? latitude;
  final double? longitude;

  User({
    required this.nickname,
    required this.city,
    required this.email,
    required this.password,
    this.latitude,
    this.longitude,
  });

  Map<String, dynamic> toJson() {
    return {
      'nickname': nickname,
      'city': city,
      'email': email,
      'password': password,
      'latitude': latitude,
      'longitude': longitude,
    };
  }
}