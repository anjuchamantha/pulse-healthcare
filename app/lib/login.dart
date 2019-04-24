import 'package:flutter/material.dart';
import 'package:pulse_healthcare/home.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:pulse_healthcare/uigradient.dart';

class LoginScreen extends StatelessWidget {
  LoginScreen({Key key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // Store media query hight to avoid lookup latency
    double mediaQueryHeight = MediaQuery.of(context).size.height;

    return Scaffold(
      appBar: AppBar(
        title: Text("Login"),
        centerTitle: true,
      ),
      body: ListView(
        children: <Widget>[
          SizedBox(
            height: mediaQueryHeight / 3,
            child: _buildTopBanner(context),
          ),
          Container(
            height: mediaQueryHeight / 2,
            child: LoginForm(),
          )
        ],
      ),
    );
  }

  /// Top Half of the screen
  Widget _buildTopBanner(BuildContext context) {
    return Container(
      alignment: Alignment.center,
      color: Theme.of(context).primaryColor,
      padding: const EdgeInsets.all(16.0),
      child: Text(
        "MediKit",
        style: TextStyle(
          fontSize: 72,
          color: Colors.white,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

/// Login Form (Stateful) - Bottom Half of the screen
class LoginForm extends StatefulWidget {
  @override
  _LoginFormState createState() => _LoginFormState();
}

class _LoginFormState extends State<LoginForm> {
  // Form key to validate form
  final _formKey = GlobalKey<FormState>();

  // Text Controllers to retrieve text
  TextEditingController _usernameController;
  TextEditingController _passwordController;

  void initState() {
    super.initState();
    _usernameController = new TextEditingController();
    _passwordController = new TextEditingController();
  }

  /// Builds a text box for the login form
  Widget _buildFormTextBox({
    String labelText,
    FormFieldValidator<String> validator,
    TextEditingController textEditingController,
    IconData icon,
    isPassword = false,
  }) {
    return Padding(
      padding: const EdgeInsets.all(8.0),
      child: Center(
        child: Center(
          child: TextFormField(
            controller: textEditingController,
            obscureText: isPassword,
            decoration: InputDecoration(
              prefixIcon: Icon(icon),
              border: OutlineInputBorder(),
              labelText: labelText,
            ),
            validator: validator,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      alignment: Alignment.center,
      margin: EdgeInsets.all(16.0),
      child: Form(
        key: _formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            _buildFormTextBox(
              labelText: "Patient ID",
              validator: _patientIdValidation,
              textEditingController: _usernameController,
              icon: FontAwesomeIcons.userAlt,
              isPassword: false,
            ),
            _buildFormTextBox(
              labelText: "Password",
              validator: _passwordIdValidation,
              textEditingController: _passwordController,
              icon: FontAwesomeIcons.key,
              isPassword: true,
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: <Widget>[
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: RaisedButton(
                    onPressed: _loginButtonPress,
                    color: Theme.of(context).accentColor,
                    child: Padding(
                      padding: const EdgeInsets.all(20.0),
                      child: Text(
                        'Login',
                        style: TextStyle(fontSize: 18, color: Colors.white),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String _patientIdValidation(String value) {
    if (value.isEmpty) {
      return 'Please enter your Patient ID';
    }
    return null;
  }

  String _passwordIdValidation(String value) {
    if (value.isEmpty) {
      return 'Please enter password';
    }
    return null;
  }

  void _loginButtonPress() {
    if (_formKey.currentState.validate()) {
      if (_usernameController.text == 'demo') {
        if (_passwordController.text == 'pass') {
          loginSuccessful();
        } else {
          loginFailed(message: "Incorrect Password");
        }
      } else {
        loginFailed(message: "User does not exist");
      }
    }
  }

  void loginFailed({String message}) {
    showDialog(
      context: context,
      builder: (_) => LoginFailedDialog(errorMessage: message),
    );
  }

  void loginSuccessful() {
    Navigator.pushReplacement(
      context,
      PageRouteBuilder(
        pageBuilder: (_, animation, ___) => FadeTransition(
              opacity: animation,
              child: HomePage(),
            ),
      ),
    );
  }
}

class LoginFailedDialog extends StatelessWidget {
  final String errorMessage;

  LoginFailedDialog({Key key, this.errorMessage}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return SimpleDialog(
      backgroundColor: UiGradient.warningColor,
      children: <Widget>[
        Container(
          margin: EdgeInsets.symmetric(vertical: 24.0),
          alignment: Alignment.center,
          child: Text(
            errorMessage,
            style: TextStyle(fontSize: 16.0),
            textAlign: TextAlign.center,
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: OutlineButton(
            onPressed: () {
              Navigator.pop(context);
            },
            child: Padding(
              padding: const EdgeInsets.all(8.0),
              child: Text(
                'OK',
                style: TextStyle(fontSize: 18),
              ),
            ),
          ),
        )
      ],
      title: Align(
        alignment: Alignment.center,
        child: Text("Login Failed"),
      ),
    );
  }
}
