package com.example;

import java.io.IOException;
import jakarta.servlet.*;
import jakarta.servlet.http.*;
import jakarta.servlet.annotation.WebServlet;

@WebServlet("/LoginServlet")
public class LoginServlet extends HttpServlet {

    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String username = request.getParameter("username");
        String password = request.getParameter("password");

        
        if (username.equals("admin") && password.equals("1234")) {

            
            HttpSession session = request.getSession();
            session.setAttribute("username", username);

           
            session.setMaxInactiveInterval(300);

            response.sendRedirect("home");
        } else {
            response.getWriter().println("<h3>Invalid login! Try again.</h3>");
        }
    }
}
