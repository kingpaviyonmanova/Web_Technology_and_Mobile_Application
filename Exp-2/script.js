function validateform() {

    let fname = document.getElementById("fname").value;
    let lname = document.getElementById("lname").value;
    let password = document.getElementById("password").value;
    let email = document.getElementById("email").value;
    let mobile = document.getElementById("mobile").value;
    let address = document.getElementById("address").value;

    fname = fname.trim();

    if (password.length < 6) {
        alert("Password must be atleast 6 characters long");
        return false;
    }

    let namepattern = /^[a-zA-Z ]+$/;
    let emailpattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    let mobilepattern = /^[0-9]{10}$/;

    if (!namepattern.test(fname) || fname.length < 6) {
        alert("First Name must contain only alphabets and atleast 6 characters long");
        return false;
    }

    if (!emailpattern.test(email)) {
        alert("Please enter a valid E-Mail ID");
        return false;
    }

    if (!mobilepattern.test(mobile)) {
        alert("Mobile number must contain only 10 digits");
        return false;
    }

    if (lname === "") {
        alert("Last Name cannot be empty");
        return false;
    }

    if (address === "") {
        alert("Address cannot be empty");
        return false;
    }

    alert("Form submitted successfully");
    return true;
}
