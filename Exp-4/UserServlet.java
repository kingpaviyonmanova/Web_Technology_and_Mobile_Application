package com.example;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.*;
import jakarta.servlet.http.*;
import jakarta.servlet.annotation.WebServlet;
@WebServlet("/UserServlet")
public class UserServlet extends HttpServlet {
 protected void doPost(HttpServletRequest request, HttpServletResponse response)
 throws ServletException, IOException {
 response.setContentType("text/html");
 PrintWriter out = response.getWriter();
 String name = request.getParameter("name");
 String age = request.getParameter("age");
 out.println("<html><body>");
 out.println("<h2>Hello, " + name + "!</h2>");
 out.println("<h3>You are " + age + " years old.</h3>");
 out.println("</body></html>");
 }
